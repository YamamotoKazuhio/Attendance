/**
 * calendar/tableUtils.js
 * - カレンダーテーブルのセル生成とステータス表示更新のユーティリティモジュール
 * - セレクトセルと入力セルの生成関数を提供
 * - ステータス表示の更新関数を提供（集計結果に基づく）
 * - シフト変更時の自動入力機能も含む
 */
import { calcRowTime, calculateTotals } from "../calc.js";
import { shiftTimes } from "../constants.js"; // shiftTimesをインポート

// ステータスの表示更新（集計結果に基づく）
export function updateStatusLabel(stats) {
    const statusLabel = document.getElementById("statusLabel");
    if (!statusLabel) return;

    let text = "", color = "", bgColor = "", borderColor = "";
    const { regBtnCount, approvedCount, totalDays } = stats;

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

// セレクトセルの生成とイベント紐付け
export function createSelectCell(options, selectedValue, isShift, isLocked) {
    const td = document.createElement("td");
    const select = document.createElement("select");
    if (isLocked) select.disabled = true;

    select.innerHTML = `<option value="">-</option>` + 
        options.map(v => `<option value="${v}" ${selectedValue === v ? "selected" : ""}>${v}</option>`).join("");

    select.onchange = () => {
        if (!isLocked) {
            const row = select.closest("tr");
            
            // シフトが変更された場合、始業・終業・休憩を自動入力する
            if (isShift && shiftTimes[select.value]) {
                const [start, end, breakTime] = shiftTimes[select.value];
                
                // 行内の各セルを取得して値をセット
                // セル構成: [0]ステータス, [1]日付, [2]特殊, [3]勤務区分, [4]就業場所, [5]シフト, [6]始業, [7]終業, [8]休憩...
                if (row.cells[6]) row.cells[6].querySelector("input").value = start;
                if (row.cells[7]) row.cells[7].querySelector("input").value = end;
                if (row.cells[8]) row.cells[8].querySelector("input").value = breakTime.toFixed(2);
            }

            calcRowTime(row);
            calculateTotals();
        }
    };

    td.appendChild(select);
    return td;
}

// 入力セルの生成とイベント紐付け
export function createInputCell(type, value, isReadOnly, isLocked) {
    const td = document.createElement("td");
    const input = document.createElement("input");
    input.type = type;
    input.value = value || "";
    input.readOnly = isReadOnly || isLocked; 
    if (isLocked) input.disabled = true;

    input.onchange = () => {
        if (!isLocked) {
            const row = input.closest("tr");
            calcRowTime(row);
            calculateTotals();
        }
    };

    td.appendChild(input);
    return td;
}