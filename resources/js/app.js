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

window.Alpine = Alpine;
Alpine.start();
