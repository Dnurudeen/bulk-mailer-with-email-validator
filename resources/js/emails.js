window.Echo.channel("emails").listen("EmailUpdated", (e) => {
    const row = document.querySelector(`tr[data-id='${e.email.id}']`);
    if (row) {
        row.querySelector(".status").textContent = e.email.status;
        row.querySelector(".disposable").textContent = e.email.is_disposable
            ? "yes"
            : "no";
        row.querySelector(".reason").textContent = e.email.reason ?? "-";
        row.querySelector(".queued-at").textContent = e.email.created_at_human;
    }
});
