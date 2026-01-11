/*
document.addEventListener("click", async (e) => {
const btn = e.target.closest("[spark\\:click]");
if (!btn) return;

const comp = btn.closest("[data-spark]");
const id = comp.dataset.id;
const action = btn.getAttribute("spark:click");

let snapshot = {};
try {
snapshot = JSON.parse(comp.dataset.snapshot || "{}");
} catch (e) {
snapshot = {};
}

const res = await fetch("/", {
method: "POST",
headers: { "Content-Type": "application/json" },
body: JSON.stringify({
_spark: 1,
component: comp.dataset.spark,
id,
snapshot,
action,
}),
}).then((r) => r.json());

comp.innerHTML = res.html;
comp.dataset.snapshot = JSON.stringify(res.snapshot || snapshot);

});
*/

// assets/spark.js
document.addEventListener("click", async (e) => {

// 1️⃣ Handle component actions (buttons)
const btn = e.target.closest("[spark\\:click]");
if (btn) {
const comp = btn.closest("[data-spark]");
if (!comp) return;

const id = comp.dataset.id;
const action = btn.getAttribute("spark:click");

// 1️⃣ Parse existing snapshot safely (needed for Counter, etc.)
let snapshot = {};
try {
  const parsed = JSON.parse(comp.dataset.snapshot || "{}");
  snapshot = Array.isArray(parsed) ? {} : parsed;
} catch (err) {
  snapshot = {};
}

console.log('1st',snapshot);

// 2️⃣ Merge/update snapshot from spark:model inputs (needed for Todo forms)
comp.querySelectorAll("[spark\\:model]").forEach((el) => {
    snapshot[el.getAttribute("spark:model")] = el.value;
});

console.log('2nd',snapshot);

// Prepare payload (e.g., for toggle/delete buttons with data-id)
let payload = [];
if (btn.dataset.id) payload.push(btn.dataset.id);

// Send AJAX request
const res = await fetch("/", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
    _spark: 1,
    component: comp.dataset.spark,
    id,
    snapshot,
    action,
    payload,
    }),
}).then((r) => r.json());

// Update component HTML and snapshot
comp.innerHTML = res.html;
comp.dataset.snapshot = JSON.stringify(res.snapshot || snapshot);

return; // stop here
}

// 2️⃣ Handle SPA navigation links
const link = e.target.closest("[spark\\:navigate]");
if (link) {
e.preventDefault();
const href = link.getAttribute("href");
if (!href) return;

const res = await fetch(href, { headers: { "X-Requested-With": "XMLHttpRequest" } });
const html = await res.text();

const app = document.getElementById("app");
if (app) app.innerHTML = html;

window.history.pushState({}, "", href);
return;
}
});

// 3️⃣ Handle back/forward browser buttons
window.addEventListener("popstate", async () => {
const res = await fetch(window.location.pathname, { headers: { "X-Requested-With": "XMLHttpRequest" } });
const html = await res.text();
const app = document.getElementById("app");
if (app) app.innerHTML = html;
});
