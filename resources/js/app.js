import "./bootstrap";
import "./echo-reverb";
import Alpine from "alpinejs";

window.Alpine = Alpine;

window.campaignProgress = function (campaignId, initialStats) {
    return {
        stats: initialStats,
        percent: 0,
        logLines: [],
        init() {
            this.updatePercent();

            Echo.leave(`campaign.${campaignId}`); // clean up previous listeners

            Echo.private(`campaign.${campaignId}`)
                .listen(".progress", (e) => {
                    console.log("📢 EVENT:", e);
                    this.stats = e.stats;
                    this.logLines.push(e.line);
                    this.updatePercent();
                })
                .error((error) => {
                    console.error("Echo error:", error);
                });
        },
        updatePercent() {
            const total =
                this.stats.pending +
                this.stats.queued +
                this.stats.sent +
                this.stats.failed;
            const completed = this.stats.sent + this.stats.failed;
            this.percent =
                total > 0 ? Math.round((completed / total) * 100) : 0;
        },
        async clearRecipients() {
            if (!confirm("Are you sure you want to clear all recipients?"))
                return;

            try {
                let response = await fetch(
                    `/campaigns/${campaignId}/recipients`,
                    {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector(
                                'meta[name="csrf-token"]'
                            ).content,
                            Accept: "application/json",
                        },
                    }
                );

                let data = await response.json();

                if (data.status === "success") {
                    alert(data.message);
                    // reset counters in UI
                    this.stats.pending = 0;
                    this.stats.queued = 0;
                    this.stats.sent = 0;
                    this.stats.failed = 0;
                    this.updatePercent();
                }
            } catch (error) {
                console.error(error);
                alert("Failed to clear recipients.");
            }
        },
    };
};

// window.Alpine = Alpine;




// 👇 Add this for validation
// 👇 Replace your stripped version with this full one
window.validationProgress = function (
    batchId,
    initialStats = {},
    initialResults = []
) {
    return {
        batchId,
        stats: {
            total: initialStats.total ?? 0,
            valid: initialStats.valid ?? 0,
            invalid: initialStats.invalid ?? 0,
            status: initialStats.status ?? "queued",
        },
        results: initialResults ?? [],
        logLines: [
            `[${new Date().toLocaleTimeString()}] Subscribed to validation.${batchId}`,
        ],
        tab: "valid",
        listName: "",

        get progress() {
            const done = this.stats.valid + this.stats.invalid;
            return this.stats.total > 0
                ? Math.round((done / this.stats.total) * 100)
                : 0;
        },

        get validList() {
            return this.results.filter((r) => r.is_valid);
        },
        get invalidList() {
            return this.results.filter((r) => !r.is_valid);
        },
        get displayList() {
            return this.tab === "valid" ? this.validList : this.invalidList;
        },
        get saveListUrl() {
            return `/validation/${this.batchId}/save-list`;
        },

        init() {
            this.logLines.push(
                `[${new Date().toLocaleTimeString()}] Listening for events`
            );

            Echo.private(`validation.${this.batchId}`)
                .listen(".progress", (e) => {
                    console.log("📢 VALIDATION EVENT:", e);

                    if (e?.stats) Object.assign(this.stats, e.stats);

                    if (e?.result) {
                        const idx = this.results.findIndex(
                            (x) => x.id === e.result.id
                        );
                        if (idx !== -1) {
                            this.results.splice(
                                idx,
                                1,
                                Object.assign(this.results[idx], e.result)
                            );
                        } else {
                            this.results.unshift(e.result);
                        }
                    }

                    if (e?.line) {
                        this.logLines.push(e.line);
                        this.$nextTick(() => {
                            const c = this.$root.querySelector(".bg-black");
                            if (c) c.scrollTop = c.scrollHeight;
                        });
                    }
                })
                .error((err) => console.error("Echo error:", err));
        },

        refresh() {
            fetch(`/validation/${this.batchId}`)
                .then(() =>
                    this.logLines.push(
                        `[${new Date().toLocaleTimeString()}] Refreshed`
                    )
                )
                .catch(console.error);
        },

        async saveList() {
            if (!this.listName.trim()) {
                alert("Enter list name");
                return;
            }
            const token = document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content");

            const res = await fetch(this.saveListUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": token,
                    Accept: "application/json",
                },
                body: JSON.stringify({
                    list_name: this.listName,
                }),
            });

            if (res.ok) {
                this.logLines.push(
                    `[${new Date().toLocaleTimeString()}] Saved list: ${
                        this.listName
                    }`
                );
                this.listName = "";
            } else {
                alert("Failed to save list");
            }
        },
    };
};

Alpine.start();
