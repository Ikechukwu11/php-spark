document.addEventListener("click", async (e) => {
// ---------- COMPONENT ACTIONS ----------
const btn = e.target.closest("[spark\\:click]");
if (btn) {
  const comp = btn.closest("[data-spark]");
  if (!comp) return;

  const id = comp.dataset.id;
  const action = btn.getAttribute("spark:click");

  // Restore snapshot
  let snapshot = {};
  try {
    const parsed = JSON.parse(comp.dataset.snapshot || "{}");
    snapshot = Array.isArray(parsed) ? {} : parsed;
  } catch (_) {
    snapshot = {};
  }

  // Merge spark:model inputs
  comp.querySelectorAll("[spark\\:model]").forEach((el) => {
    snapshot[el.getAttribute("spark:model")] = el.value;
  });

  // Payload (toggle/delete ids)
  let payload = [];
  if (btn.dataset.id) payload.push(btn.dataset.id);
  if (btn.dataset.page) payload.push(btn.dataset.page);


  const res = await fetch("/", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-Spark": "1",
    },
    body: JSON.stringify({
      _spark: 1,
      component: comp.dataset.spark,
      id,
      snapshot,
      action,
      payload,
    }),
  }).then((r) => r.json());

  comp.innerHTML = res.html;
  comp.dataset.snapshot = JSON.stringify(res.snapshot || snapshot);

  // 🔥 Hybrid event re-render
  if (res.events) {
    res.events.forEach((ev) => {
      // ---------- Normal event refresh ----------
      if (ev.event && ev.component && ev.event !== "__refresh_once") {
        document
          .querySelectorAll(`[data-spark="${ev.component}"]`)
          .forEach((c) => {
            c.dispatchEvent(
              new CustomEvent("spark:refresh", {
                bubbles: true,
                detail: ev.payload,
              })
            );
          });
      }

      // ---------- refresh_once event (bundled HTML) ----------
      if (ev.event === "__refresh_once" && Array.isArray(ev.payload)) {
        ev.payload.forEach((bundle) => {
          document
            .querySelectorAll(`[data-spark="${bundle.component}"]`)
            .forEach((c) => {
              const id = c.dataset.id;
              const newId = bundle.id;
              // Create temporary container to parse new HTML
              const tmp = document.createElement("div");
              tmp.innerHTML = bundle.html;
              tmp.dataset.spark = bundle.component;
              tmp.dataset.id = newId;

              // Patch keyed elements dynamically
              patchKeys(comp, tmp);

              // Diff against current DOM
              diffDOM(c, tmp);

              // Set snapshot safely
              if (
                bundle.snapshot &&
                typeof bundle.snapshot === "object" &&
                !Array.isArray(bundle.snapshot)
              ) {
                c.dataset.snapshot = JSON.stringify(bundle.snapshot);
              } else {
                c.dataset.snapshot = "{}";
              }

              // Optional: trigger mounted hook
              // c.dispatchEvent(new CustomEvent("spark:mounted", { bubbles: true }));
            });
        });
      }
    });
  }

  return;
}


// ---------- SPA NAVIGATION ----------
const link =
  e.target.closest("[spark\\:navigate]") ||
  e.target.closest("[spark\\:navigate\\.hover]");
if (link) {
  console.log(link);
  e.preventDefault();
  const href = link.getAttribute("href");
    const app = document.getElementById("app");
    let html;

if (prefetchCache[href]) {
    html = prefetchCache[href]; // use prefetched HTML
  } else {
  const res = await fetch(href, { headers: { "X-Spark": "1" } });
  html = await res.text();
  }

  app.innerHTML = html;
  history.pushState({}, "", href);

  // 🔥 Re-run lazy hydration for any new components
  lazyLoad();
}
});

// Inside your existing document.addEventListener("click") block
// Also add input handler for spark:change / spark:debounce
document.addEventListener("input", (e) => {
    const el = e.target.closest("[spark\\:change][spark\\:debounce]");
    if (!el) return;

    const comp = el.closest("[data-spark]");
    if (!comp) return;

    const action = el.getAttribute("spark:change");
    const delay = parseInt(el.getAttribute("spark:debounce")) || 300;
    clearTimeout(el._debounceTimer);

    el._debounceTimer = setTimeout(async () => {
      /*
        let snapshot = {};
        try { snapshot = JSON.parse(comp.dataset.snapshot || "{}"); } catch (_) {}

        snapshot[el.getAttribute("spark:model")] = el.value;

        */

        // Restore snapshot
  let snapshot = {};
  try {
    const parsed = JSON.parse(comp.dataset.snapshot || "{}");
    snapshot = Array.isArray(parsed) ? {} : parsed;
  } catch (_) {
    snapshot = {};
  }

  // Merge spark:model inputs
  comp.querySelectorAll("[spark\\:model]").forEach((el) => {
    snapshot[el.getAttribute("spark:model")] = el.value;
  });

        const res = await fetch("/", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-Spark": "1" },
            body: JSON.stringify({
                _spark: 1,
                component: comp.dataset.spark,
                id: comp.dataset.id,
                snapshot,
                action,
                payload: [el.value] // pass search term
            }),
        }).then(r => r.json());

        comp.innerHTML = res.html;
        comp.dataset.snapshot = JSON.stringify(res.snapshot || snapshot);
    }, delay);
});


// ---------- COMPONENT REFRESH HANDLER ----------
document.addEventListener("spark:refresh", async (e) => {
const comp = e.target.closest("[data-spark]");
if (!comp) return;

const snapshot = JSON.parse(comp.dataset.snapshot || "{}");

const res = await fetch("/", {
    method: "POST",
    headers: {
    "Content-Type": "application/json",
    "X-Spark": "1",
    },
    body: JSON.stringify({
    _spark: 1,
    component: comp.dataset.spark,
    id: comp.dataset.id,
    snapshot,
    action: "__refresh",
    payload: e.detail ? [e.detail] : [],
    }),
}).then((r) => r.json());

comp.innerHTML = res.html;
comp.dataset.snapshot = JSON.stringify(res.snapshot || snapshot);
});

//----------- PREFETCH ON HOVER -----------------
document.addEventListener("mouseover", (e) => {
  const link = e.target.closest("[spark\\:navigate\\.hover]");
  if (!link) return;

  const url = link.getAttribute("href");
  if (!url) return;

  // Delay prefetch to avoid unnecessary network requests
  clearTimeout(link._prefetchTimer);
  link._prefetchTimer = setTimeout(() => {
    prefetchPage(url);
  }, 200); // 100ms delay
});


document.addEventListener("DOMContentLoaded", () => {
lazyLoad()
});


// ----------------- DOM DIFFING -----------------
function preserveInput(oldNode, newNode) {
    if ((oldNode.tagName === 'INPUT' || oldNode.tagName === 'TEXTAREA') &&
        newNode.tagName === oldNode.tagName) {
        newNode.value = oldNode.value;
        if (oldNode.selectionStart !== undefined) {
            newNode.setSelectionRange(oldNode.selectionStart, oldNode.selectionEnd);
        }
    }
}

function diffDOM(oldNode, newNode) {
    if (!oldNode || !newNode) return;

    // Text node update
    if (oldNode.nodeType === Node.TEXT_NODE && newNode.nodeType === Node.TEXT_NODE) {
        if (oldNode.textContent !== newNode.textContent) {
            oldNode.textContent = newNode.textContent;
        }
        return;
    }

    // Skip Spark child components unless explicitly refreshed
    if (oldNode.dataset?.sparkId && oldNode.dataset.sparkId !== newNode.dataset.sparkId) {
        oldNode.replaceWith(newNode.cloneNode(true));
        return;
    }

    // Update attributes
    if (oldNode.nodeType === Node.ELEMENT_NODE && newNode.nodeType === Node.ELEMENT_NODE) {
        Array.from(newNode.attributes).forEach(attr => {
            if (oldNode.getAttribute(attr.name) !== attr.value) {
                oldNode.setAttribute(attr.name, attr.value);
            }
        });
        Array.from(oldNode.attributes).forEach(attr => {
            if (!newNode.hasAttribute(attr.name)) oldNode.removeAttribute(attr.name);
        });
    }

    // Recurse children
    const oldChildren = Array.from(oldNode.childNodes);
    const newChildren = Array.from(newNode.childNodes);
    const max = Math.max(oldChildren.length, newChildren.length);

    for (let i = 0; i < max; i++) {
        if (!oldChildren[i] && newChildren[i]) {
            oldNode.appendChild(newChildren[i].cloneNode(true));
        } else if (oldChildren[i] && !newChildren[i]) {
            oldNode.removeChild(oldChildren[i]);
        } else if (oldChildren[i] && newChildren[i]) {
            preserveInput(oldChildren[i], newChildren[i]);
            diffDOM(oldChildren[i], newChildren[i]);
        }
    }if (newNode.querySelector?.("[data-active-page]")) {
      requestAnimationFrame(() => {
        const active = oldNode.querySelector("[data-active-page]");
        if (active)
          active.scrollIntoView({ behavior: "smooth", block: "nearest" });
      });
    }


}

function patchKeys(oldRoot, newRoot) {
  const oldMap = {};

  // Map all old keyed elements
  oldRoot.querySelectorAll("[data-spark-key]").forEach((el) => {
    oldMap[el.dataset.sparkKey] = el;
  });

  // Iterate over new keyed elements
  newRoot.querySelectorAll("[data-spark-key]").forEach((newEl) => {
    const key = newEl.dataset.sparkKey;
    if (!key) return;

    const oldEl = oldMap[key];
    if (oldEl) {
      // Merge changes instead of full replacement
      diffDOM(oldEl, newEl);
      delete oldMap[key]; // mark as processed
    } else {
      // New element: insert at correct position
      if (newEl.parentNode) {
        const parentSelector = newEl.parentNode.closest("[data-spark]");
        if (parentSelector) parentSelector.appendChild(newEl);
      }
    }
  });

  // Remove deleted elements
  Object.values(oldMap).forEach((el) => el.remove());
}

function lazyLoad(root = document) {
  // Find all placeholders
  const lazyPlaceholders = document.querySelectorAll("[spark\\:lazy]");

  lazyPlaceholders.forEach((el) => {
    el.classList.add("spark-lazy","is-loading");
    const lazyConfig = JSON.parse(el.dataset.lazy || "{}");
    const componentName = el.dataset.component;
    const snapshot = JSON.parse(el.dataset.snapshot || "{}");

    // Determine trigger
    const trigger = lazyConfig.on || "idle";

    const hydrate = async () => {
      // Fetch rendered component from server
      const res = await fetch("/", {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-Spark": "1" },
        body: JSON.stringify({
          _spark: 1,
          component: componentName,
          id: el.id,
          snapshot,
          action: "__hydrate",
          payload: [],
        }),
      });
      const data = await res.json();

      // Replace skeleton with real component
      // -------------------------------
      // Full replacement on first hydration
      // -------------------------------
      // Replace skeleton with full component
      // el.innerHTML = data.html;

      const content = el.querySelector(".spark-content-layer");
      content.innerHTML = data.html;

      // Attach snapshot and any required props for Spark
      el.dataset.snapshot = JSON.stringify(data.snapshot || snapshot);
      el.classList.remove("spark-lazy","is-loading");
      el.classList.add("spark-lazy","is-ready");

    };

    // -------------------- Trigger Handling --------------------
    if (trigger === "idle") {
      // Wait until browser is idle
      if ("requestIdleCallback" in window) {
        requestIdleCallback(hydrate);
      } else {
        setTimeout(hydrate, 100); // fallback
      }
    } else if (trigger === "visible") {
      // Use IntersectionObserver to hydrate when visible
      const io = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
          hydrate();
          io.disconnect();
        }
      });
      io.observe(el);
    } else if (trigger === "manual") {
      // Leave for manual hydration (can call hydrate() later)
      el.hydrate = hydrate;
    } else {
      // default immediate hydration
      hydrate();
    }
  });
}

const prefetchCache = {};

async function prefetchPage(url) {
  if (prefetchCache[url]) return; // already prefetched
  try {
    const res = await fetch(url, { headers: { "X-Spark": "1" } });
    const html = await res.text();
    prefetchCache[url] = html;
  } catch (err) {
    console.error("Prefetch failed for", url, err);
  }
}



window.addEventListener("popstate", async () => {
  const res = await fetch(location.pathname, { headers: { "X-Spark": "1" } });
  document.getElementById("app").innerHTML = await res.text();

  // Re-run lazy hydration for the new content
  lazyLoad();
});
