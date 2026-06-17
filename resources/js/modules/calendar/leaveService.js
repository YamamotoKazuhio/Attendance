/**
 * 休暇申請を送信する関数
 */

export function submitLeaveApplication() {
    const leaveForm = document.getElementById("leaveForm");
    const typeSelect = document.getElementById("leave_type");
    if (!leaveForm || !typeSelect) return;

    const remaining = parseFloat(window.dbRemainingPaid) || 0;
    const requested = parseFloat(typeSelect.options[typeSelect.selectedIndex].getAttribute("data-days")) || 0;

    if (typeSelect.value === "有給" && requested > remaining) {
        alert(`有給残数が不足しています。`);
        return;
    }

    fetch("/attendance/leave-apply", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            target_date: document.getElementById("leave_date")?.value,
            type: typeSelect.value,
            days_count: requested,
            reason: document.getElementById("leave_reason")?.value,
        }),
    })
    .then(res => res.json())
    .then(() => { alert("申請完了"); window.location.reload(); })
    .catch(err => console.error(err));
}