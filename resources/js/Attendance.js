/* メイン：各モジュールをimportしてwindowに公開 */
import {
    saveBasicInfo,
    saveData,
    submitLeaveApplication,
} from "./modules/actions.js";
import { calcRowTime, calculateTotals } from "./modules/calc.js";
import { loadData } from "./modules/calendar/tableRenderer.js";
import * as constants from "./modules/constants.js";

//各機能を window に公開 (HTMLのインラインスクリプトから呼ぶため)
window.saveBasicInfo = saveBasicInfo;
window.saveData = saveData;
window.submitLeaveApplication = submitLeaveApplication;
window.calcRowTime = calcRowTime;
window.calculateTotals = calculateTotals;
window.loadData = loadData;
window.Constants = constants;
/**
 * 月を移動して再読み込みする関数 (windowに公開)
 */
window.changeMonth = function (offset) {
    if (!window.currentMonthStr) return;
    
    // 現在の月文字列からDateオブジェクトを生成
    const [year, month] = window.currentMonthStr.split("-").map(Number);
    const date = new Date(year, month - 1 + offset, 1);
    
    // 新しい月文字列を作成
    const newMonthStr = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}`;
    
    // ページ遷移を強制 (これによりLaravelのControllerが新しいデータを取得し、ページが再ロードされます)
    window.location.href = `/attendance?month=${newMonthStr}`;
};

/**
 * 基本情報を手動で保存する関数 (windowに公開)
 */
async function saveBasicInfoManual() {
    const deptEl = document.getElementById("department");
    const commuteEl = document.getElementById("commuteCost");

    const dept = deptEl ? deptEl.value : "";
    const commute = commuteEl ? commuteEl.value : 0;

    try {
        const res = await fetch("/attendance/save-basic-info", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                ).content,
            },
            body: JSON.stringify({
                department: dept,
                commute_fee: commute,
            }),
        });

        if (res.ok) {
            alert("保存しました。");
            // 保存後、再計算を実行
            if (typeof window.calculateTotals === "function") {
                window.calculateTotals();
            }
        } else {
            alert("保存に失敗しました。");
        }
    } catch (err) {
        console.error("Save Error:", err);
        alert("通信エラーが発生しました。");
    }
}

// windowへ登録
window.saveBasicInfoManual = saveBasicInfoManual;
window.updateBasicInfo = saveBasicInfoManual; // HTML側の onchange に合わせるための別名登録

document.addEventListener("DOMContentLoaded", () => {
    console.log("Attendance System: All Modules Loaded.");

    // ナビゲーションボタンのイベント登録 (HTMLにIDがある場合のみ)
    document
        .getElementById("btnPrevMonth")
        ?.addEventListener("click", () => window.changeMonth(-1));
    document
        .getElementById("btnNextMonth")
        ?.addEventListener("click", () => window.changeMonth(1));
    document
        .getElementById("btnCurrentMonth")
        ?.addEventListener("click", () => {
            window.currentMonthStr = new Date().toISOString().slice(0, 7);
            loadData();
        });

    // 初期描画
    if (typeof loadData === "function") {
        loadData();
    }
});
