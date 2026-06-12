/**
 * 勤怠計算モジュール
 * resources/js/modules/calc.js
 */
import { attendanceTime } from "./constants.js";

/**
 * 1行分の勤務時間・残業時間を計算する
 */
export function calcRowTime(row) {
    // [安全策] 行がない、または列数が11未満なら計算を中止
    if (!row || row.cells.length < 11) return;

    const att = row.cells[3]?.querySelector("select")?.value;
    const start = row.cells[6]?.querySelector("input")?.value;
    const end = row.cells[7]?.querySelector("input")?.value;
    const rest = parseFloat(row.cells[8]?.querySelector("input")?.value) || 0;

    const otCell = row.cells[9]?.querySelector("input");
    const wkCell = row.cells[10]?.querySelector("input");

    // 勤務区分ごとの固定労働時間処理
    if (att && attendanceTime[att]) {
        if (otCell) otCell.value = "0.00";
        if (wkCell) wkCell.value = attendanceTime[att].toFixed(2);
    }
    // 開始・終了時間から計算する処理
    else if (start && end) {
        let [sh, sm] = start.split(":").map(Number);
        let [eh, em] = end.split(":").map(Number);

        let sMin = sh * 60 + sm;
        let eMin = eh * 60 + em;

        // 日付をまたぐ場合の処理
        if (eMin < sMin) eMin += 1440;

        let wkH = (eMin - sMin - rest * 60) / 60;
        if (wkH < 0) wkH = 0;

        if (wkCell) wkCell.value = wkH.toFixed(2);
        if (otCell) otCell.value = Math.max(0, wkH - 8).toFixed(2);
    }
}

/**
 * 全体の合計時間・通勤費を計算し、画面に反映する
 */
export function calculateTotals() {
    const commuteUnitEl = document.getElementById("commuteCost");
    
    // デバッグログ
    console.log("DEBUG: commuteUnitEl =", commuteUnitEl);
    console.log("DEBUG: commuteValue =", commuteUnitEl ? commuteUnitEl.value : "null");

    let totalOvertime = 0;
    let totalWorkTime = 0;
    let workDaysCount = 0;

    const commuteDailyRate = commuteUnitEl ? parseFloat(commuteUnitEl.value) || 0 : 0;
    const tableBody = document.getElementById("tableBody");
    
    if (!tableBody) {
        console.warn("TableBody element not found.");
        return;
    }

    const countableTypes = ["出勤", "遅刻", "早退", "午前半休", "午後半休", "フレックス", "休出"];

    // テーブルの行をスキャン
    [...tableBody.rows].forEach((r) => {
        // 特殊行・メイン行ともに11セルであることを前提
        if (r.cells.length < 11) return;

        // 残業時間と労働時間の集計
        const otInput = r.cells[9]?.querySelector("input");
        totalOvertime += otInput ? parseFloat(otInput.value) || 0 : 0;

        const wkInput = r.cells[10]?.querySelector("input");
        totalWorkTime += wkInput ? parseFloat(wkInput.value) || 0 : 0;

        // 出勤日数のカウント
        const attSelect = r.cells[3]?.querySelector("select");
        if (attSelect && countableTypes.includes(attSelect.value)) {
            workDaysCount++;
        }
    });

    // 画面への反映
    const totalOvertimeEl = document.getElementById("totalOvertime");
    const totalWorkTimeEl = document.getElementById("totalWorkTime");
    const totalCommuteCostEl = document.getElementById("totalCommuteDisplay"); // HTML側のIDと一致
    
    if (totalOvertimeEl) totalOvertimeEl.textContent = totalOvertime.toFixed(2);
    if (totalWorkTimeEl) totalWorkTimeEl.textContent = totalWorkTime.toFixed(2);

    if (totalCommuteCostEl) {
        const total = workDaysCount * commuteDailyRate;
        totalCommuteCostEl.textContent = `${total.toLocaleString()} 円`;
    } else {
        console.error("表示用要素 'totalCommuteDisplay' が見つかりません。");
    }
}