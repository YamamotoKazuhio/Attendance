/**
 * calendar/tableRowBuilder.js
 * - カレンダーテーブルの行を生成するモジュール 
 * - 各行のセルを作成し、データベースからのエントリーに基づいて値を設定
 * - 管理者と一般ユーザーで表示内容を切り替え
 * - 時間計算のためのクラスや属性を適切に設定
 */

import { calcRowTime } from "../calc.js";
import { shiftTimes } from "../constants.js";
import { createInputCell, createSelectCell } from "./tableUtils.js";

/**
 * ステータスボタンの見た目を生成する共通関数
 */
function renderStatusButton(button, dbEntry, isApproved) {
    button.className = "status-btn";
    button.style.cssText = "display: block; width: 100%; cursor: pointer;";
    
    // 状態に応じたテキストと色の設定
    if (isApproved) {
        button.textContent = "承認済み";
        button.style.setProperty("background-color", "#28a745", "important");
        button.style.setProperty("color", "#ffffff", "important");
        button.style.setProperty("border-color", "#28a745", "important");
    } else {
        const currentStatus = dbEntry?.status || "登録";
        button.textContent = currentStatus;
        if (currentStatus === "承認待ち") {
            button.style.setProperty("background-color", "orange", "important");
        }
    }
}

/**
 * カレンダーテーブルの行を生成する関数
 */
export function createRow(
    day,
    dbEntry,
    year,
    month,
    dateKey,
    isAdmin,
    isLocked,
    workTypes,
    workPlaces,
) {
    const tableBody = document.getElementById("tableBody");
    const tr = document.createElement("tr");
    tr.setAttribute("data-date", dateKey);

    // 承認済み判定：ステータスが「承認済み」ならロックとして扱う
    const isApproved = dbEntry?.status === "承認済み";
    // 管理者によるロック、または承認済みなら入力を無効化
    const effectiveLocked = isLocked || isApproved;

    // 行の色設定
    const dateObj = new Date(year, month - 1, day);
    const dayOfWeek = dateObj.getDay();
    const isHoliday = window.holidays && window.holidays[dateKey];

    if (dayOfWeek === 0 || isHoliday)
        tr.classList.add(isHoliday ? "holiday" : "sunday");
    else if (dayOfWeek === 6) tr.classList.add("saturday");
    if (dbEntry?.special_type && dbEntry.special_type !== "")
        tr.classList.add("special-row");
    
    // 承認済みの場合、操作不能用のクラスを付与
    if (isApproved) tr.classList.add("row-disabled");

    // [0] ステータスセル（管理者・一般ユーザー共通でJSによる非同期ステータス切り替えに統一）
    const statusCell = document.createElement("td");
    const statusBtn = document.createElement("button");
    renderStatusButton(statusBtn, dbEntry, isApproved);
    
    // 承認済みの場合はボタン自体を無効化（ただし管理者の場合は必要に応じて操作できるようにすることも可能ですが、基本仕様に合わせます）
    if (isApproved && !isAdmin) {
        statusBtn.disabled = true;
    } else {
        statusBtn.onclick = async () => {
            const currentText = statusBtn.textContent;
            let nextText = "承認待ち";

            if (currentText === "承認待ち") {
                nextText = "登録";
            } else if (currentText === "承認済み" && isAdmin) {
                nextText = "登録";
            }

            statusBtn.textContent = nextText;
            statusBtn.style.setProperty(
                "background-color", 
                nextText === "承認待ち" ? "orange" : (nextText === "承認済み" ? "#28a745" : ""), 
                "important"
            );

            // 共通の非同期保存処理を呼び出し
            if (typeof window.saveData === "function") {
                await window.saveData();
            }
        };
    }
    statusCell.appendChild(statusBtn);
    tr.appendChild(statusCell);

    // [1] 日付
    const dateCell = document.createElement("td");
    dateCell.textContent = `${month}/${day}(${["日", "月", "火", "水", "木", "金", "土"][dayOfWeek]})`;
    tr.appendChild(dateCell);

    // [2] 特殊行ボタン
    const specialCell = document.createElement("td");
    const specialBtn = document.createElement("button");
    specialBtn.textContent = "特殊";
    specialBtn.className = "special-add-btn";
    specialBtn.disabled = effectiveLocked; 
    specialBtn.onclick = () => tr.after(createExtraRow(dateKey));
    specialCell.appendChild(specialBtn);
    tr.appendChild(specialCell);

    // 入力セル（第4引数に effectiveLocked を渡して一括制御）
    tr.appendChild(createSelectCell(workTypes, dbEntry?.attendance_type, false, effectiveLocked));
    tr.appendChild(createSelectCell(workPlaces, dbEntry?.workplace, false, effectiveLocked));
    tr.appendChild(createSelectCell(Object.keys(shiftTimes), dbEntry?.shift, true, effectiveLocked));
    tr.appendChild(createInputCell("time", dbEntry?.start_time?.substring(0, 5), false, effectiveLocked));
    tr.appendChild(createInputCell("time", dbEntry?.end_time?.substring(0, 5), false, effectiveLocked));
    tr.appendChild(createInputCell("text", dbEntry?.break_time || "0.00", true, effectiveLocked));
    tr.appendChild(createInputCell("text", dbEntry?.overtime || "0.00", true, effectiveLocked));
    tr.appendChild(createInputCell("text", dbEntry?.work_time || "0.00", true, effectiveLocked));

    tableBody.appendChild(tr);
}