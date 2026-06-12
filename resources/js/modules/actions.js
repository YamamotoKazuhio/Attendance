/**
 * サーバー通信アクションモジュール
 */
import { getCsrfToken } from "./utils.js";

// 1. 各関数を定義する (※ここでは export を書かない)
async function saveData() {
    if (!window.displayUserId) {
        const userIdInput = document.getElementById("user_id_field");
        window.displayUserId = userIdInput ? userIdInput.value : null;
    }

    if (!window.displayUserId) {
        alert("エラーが発生しました: ユーザーIDが指定されていません。");
        return;
    }

    const tableBody = document.getElementById("tableBody");
    const rows = tableBody ? Array.from(tableBody.querySelectorAll("tr")) : [];

    const data = rows.map((row) => {
        // 1. ボタン要素を特定
        const statusBtn = row.querySelector(".status-btn");

        return {
            work_date: row.getAttribute("data-date"),
            // 2. テキスト内容を確実に取得
            status: statusBtn ? statusBtn.textContent.trim() : "登録",

            special_type: row.cells[2]?.querySelector("select")?.value || "",
            attendance_type: row.cells[3]?.querySelector("select")?.value || "",
            workplace: row.cells[4]?.querySelector("select")?.value || "",
            shift: row.cells[5]?.querySelector("select")?.value || "",
            start_time: row.cells[6]?.querySelector("input")?.value || null,
            end_time: row.cells[7]?.querySelector("input")?.value || null,
            break_time: row.cells[8]?.querySelector("input")?.value || 0,
            overtime: row.cells[9]?.querySelector("input")?.value || 0,
            work_time: row.cells[10]?.querySelector("input")?.value || 0,
        };
    });
    try {
        const response = await fetch("/attendance/save-all", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN":
                    getCsrfToken() ||
                    document.querySelector('meta[name="csrf-token"]')?.content,
            },
            body: JSON.stringify({
                attendances: data,
                user_id: window.displayUserId,
            }),
        });
        if (!response.ok) throw new Error("保存に失敗しました");
        alert("勤怠データを保存しました。");
    } catch (err) {
        alert("エラーが発生しました: " + err.message);
    }
}

async function submitLeaveApplication() {
    const typeSelect = document.getElementById("leave_type");
    const targetDateInput = document.getElementById("leave_date");
    const reasonInput = document.getElementById("leave_reason");

    if (!typeSelect || !targetDateInput) return;

    const payload = {
        target_date: targetDateInput.value,
        type: typeSelect.value,
        days_count:
            parseFloat(
                typeSelect.options[typeSelect.selectedIndex]?.getAttribute(
                    "data-days",
                ),
            ) || 1.0,
        reason: reasonInput?.value || "勤務表から申請",
    };

    try {
        const res = await fetch("/attendance/leave-apply", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN":
                    getCsrfToken() ||
                    document.querySelector('meta[name="csrf-token"]')?.content,
            },
            body: JSON.stringify(payload),
        });
        if (!res.ok) throw new Error("申請に失敗しました");
        alert("申請が完了しました。");
        window.location.reload();
    } catch (err) {
        alert("申請に失敗しました: " + err.message);
    }
}

async function saveBasicInfo() {
    const commuteCostEl = document.getElementById("commuteCost");
    const departmentEl = document.getElementById("department");

    let userId = window.displayUserId;
    if (!userId) {
        const idField = document.querySelector('input[name="user_id"]');
        userId = idField ? idField.value : null;
    }

    const payload = {
        commute_fee: commuteCostEl ? commuteCostEl.value : 0,
        department: departmentEl ? departmentEl.value : "",
        user_id: userId,
    };

    console.log("送信しようとしているPayload:", payload); // <-- これで確認！

    if (!userId) {
        alert("エラー: ユーザーIDが取得できませんでした。");
        return;
    }

    try {
        const response = await fetch("/attendance/save-basic-info", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                ).content,
            },
            body: JSON.stringify(payload),
        });
        if (!response.ok) throw new Error("保存に失敗しました");
        alert("保存しました。");
    } catch (err) {
        alert("エラーが発生しました。");
    }
}

// 2. 最後にまとめて1回だけエクスポートする
export { saveBasicInfo, saveData, submitLeaveApplication };
