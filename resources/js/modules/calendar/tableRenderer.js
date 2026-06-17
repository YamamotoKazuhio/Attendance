/**
 * calendar/tableRowBuilder.js
 * - カレンダーテーブルの行を生成するモジュール
 * - 各行のセルを作成し、データベースからのエントリーに基づいて値を設定
 * - 管理者と一般ユーザーで表示内容を切り替え
 * - 時間計算のためのクラスや属性を適切に設定
 * - ステータスボタンの見た目を生成する共通関数も含む
 */

import { createRow } from "./tableRowBuilder.js";
import { workTypes, workPlaces } from "../constants.js";
import { updateStatusLabel } from "./uiUpdater.js"; // uiUpdater をインポート

export function loadData() {
    const tableBody = document.getElementById("tableBody");
    if (!tableBody) return;
    tableBody.innerHTML = "";

    const db = window.savedDataFromServer || [];
    const dbMap = db.reduce((acc, item) => ({ ...acc, [item.work_date]: item }), {});
    
    const [year, month] = (window.currentMonthStr || new Date().toISOString().slice(0, 7)).split("-").map(Number);
    const lastDay = new Date(year, month, 0).getDate();

    let regBtnCount = 0;
    let approvedCount = 0;

    for (let d = 1; d <= lastDay; d++) {
        const dateKey = `${year}-${String(month).padStart(2, "0")}-${String(d).padStart(2, "0")}`;
        const entry = dbMap[dateKey];
        
        // 行の生成
        createRow(d, entry, year, month, dateKey, window.isAdmin, window.isLocked, workTypes, workPlaces);

        // ステータスの集計 (uiUpdaterに渡すデータ)
        const status = entry?.status || "登録";
        if (status === "登録") regBtnCount++;
        else if (status === "承認済み") approvedCount++;
    }

    // ★重要: ここで集計結果を uiUpdater に渡す
    updateStatusLabel({ regBtnCount, approvedCount, totalDays: lastDay });
}