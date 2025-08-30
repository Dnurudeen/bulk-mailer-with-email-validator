// 👇 Add this for validation
window.validationProgress = function (batchId, initialStats) {
    return {
        stats: initialStats,
        percent: 0,
        logLines: [],
        tab: "valid",

        init() {
            this.updatePercent();

            Echo.private(`validation.${batchId}`)
                .listen(".progress", (e) => {
                    console.log("📢 VALIDATION EVENT:", e);
                    this.stats = e.stats;
                    this.logLines.push(e.line);
                    this.updatePercent();
                })
                .error((err) => console.error("Echo error:", err));
        },

        updatePercent() {
            const total = this.stats.total || 0;
            const completed = this.stats.valid + this.stats.invalid;
            this.percent =
                total > 0 ? Math.round((completed / total) * 100) : 0;
        },
    };
};
