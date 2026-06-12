export function updateStatusLabel({ regBtnCount, approvedCount, totalDays }) {
    const statusLabel = document.getElementById("statusLabel");
    if (!statusLabel) return;

    let text = "", color = "", bgColor = "", borderColor = "";

    if (regBtnCount === totalDays) {
        text = "未登録";
        color = "#666"; bgColor = "#eee"; borderColor = "#ccc";
    } else if (approvedCount === totalDays) {
        text = "✅ 承認済み（修正不可）";
        color = "#2e7d32"; bgColor = "#e6ffed"; borderColor = "#2e7d32";
    } else if (approvedCount > 0) {
        text = "⚠️ 承認済み（一部のみ）";
        color = "#f57c00"; bgColor = "#fff3e0"; borderColor = "#f57c00";
    } else {
        text = "🚩 確定（承認待ち）";
        color = "#d32f2f"; bgColor = "#fff5f5"; borderColor = "#d32f2f";
    }

    statusLabel.textContent = text;
    statusLabel.style.color = color;
    statusLabel.style.backgroundColor = bgColor;
    statusLabel.style.border = `1px solid ${borderColor}`;
    statusLabel.style.padding = "2px 8px";
    statusLabel.style.borderRadius = "4px";
    statusLabel.style.fontWeight = "bold";
}

/**
 * 有給管理状況詳細モーダルを表示する関数
 */
export function openDetailModal() {
    const paidLeaveInfo = window.paidLeaveData || { total_given: 10, total_used: 0, balance: 10, history: [] };
    const historyStr = paidLeaveInfo.history.map(h => `${h.date} | ${h.days}日 | ${h.type}`).join("\n");
    alert(`【有給休暇 管理状況】\n累計付与: ${paidLeaveInfo.total_given} 日\n累計消化: ${paidLeaveInfo.total_used} 日\n現在残高: ${paidLeaveInfo.balance} 日\n\n[付与履歴]\n${historyStr}`);
}