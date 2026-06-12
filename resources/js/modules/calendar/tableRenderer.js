/**
 * tableRenderer.js - テーブル生成と描画ロジック
 */
import { calcRowTime } from "../calc.js"; 
import { saveData } from "../actions.js";
import { workTypes, workPlaces, shiftTimes } from "../constants.js";
import { updateStatusLabel } from "./uiUpdater.js";

// データを読み込みテーブルを再生成する
export function loadData() {
    const tableBody = document.getElementById("tableBody");
    if (!tableBody) return;
    tableBody.innerHTML = "";

    const dateStr = window.currentMonthStr || new Date().toISOString().slice(0, 7);
    const [year, month] = dateStr.split("-").map(Number);
    const lastDay = new Date(year, month, 0).getDate();

    const db = window.savedDataFromServer || [];
    const dbMap = db.reduce((acc, item) => ({ ...acc, [item.work_date]: item }), {});

    let regBtnCount = 0;
    let approvedCount = 0;

    for (let d = 1; d <= lastDay; d++) {
        const dateKey = `${year}-${String(month).padStart(2, "0")}-${String(d).padStart(2, "0")}`;
        const entry = dbMap[dateKey];
        createRow(d, entry, year, month, dateKey);

        const status = entry?.status || "登録";
        if (status === "登録") regBtnCount++;
        else if (status === "承認済み") approvedCount++;
    }

    updateStatusLabel({ regBtnCount, approvedCount, totalDays: lastDay });
    const rows = tableBody.querySelectorAll("tr");
    rows.forEach(row => calcRowTime(row));
    
    // ここで合計を計算
    setTimeout(() => {
        if (typeof window.calculateTotals === 'function') {
            window.calculateTotals();
        } else {
            console.error("calculateTotals is not defined in window");
        }
    }, 0);
}

/**
 * 勤怠表の1行を生成する関数
 */
export function createRow(day, dbEntry, year, month, dateKey) {
    const tableBody = document.getElementById("tableBody");
    const tr = document.createElement("tr");
    tr.setAttribute("data-date", dateKey);

    const dateObj = new Date(year, month - 1, day);
    const dayOfWeek = dateObj.getDay();
    const isHoliday = window.holidays && window.holidays[dateKey];
    const isLocked = dbEntry?.status === "承認済み";
    const isAdmin = window.isAdmin === true;

    if (dayOfWeek === 0 || isHoliday) tr.classList.add(isHoliday ? "holiday" : "sunday");
    else if (dayOfWeek === 6) tr.classList.add("saturday");
    if (dbEntry?.special_type && dbEntry.special_type !== "") tr.classList.add("special-row");

    // [0] ステータスセル
    const statusCell = document.createElement("td");
    if (isAdmin) {
        const form = document.createElement('form');
        form.action = `/admin/attendance/toggle-status/${dbEntry?.id || 0}`;
        form.method = 'POST';
        form.style.margin = "0";
        form.innerHTML = `
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
            <button type="submit" class="btn btn-sm ${isLocked ? 'btn-success' : 'btn-warning'}" style="display:block; width:100%; cursor:pointer;">
                ${dbEntry?.status || '登録'}
            </button>`;
        statusCell.appendChild(form);
    } else {
        const statusBtn = document.createElement("button");
        statusBtn.className = "status-btn";
        if (isLocked) {
            statusBtn.disabled = true;
            statusBtn.textContent = "承認済み";
            statusBtn.style.backgroundColor = "#28a745";
            statusBtn.style.color = "white";
        } else {
            const currentStatus = dbEntry?.status || "登録";
            statusBtn.textContent = currentStatus;
            statusBtn.style.backgroundColor = (currentStatus === "承認待ち") ? "orange" : "";
            statusBtn.onclick = async () => {
                const isNowRegistered = statusBtn.textContent === "登録";
                statusBtn.textContent = isNowRegistered ? "承認待ち" : "登録";
                statusBtn.style.backgroundColor = isNowRegistered ? "orange" : "";
                await saveData();
            };
        }
        statusBtn.style.cssText += "display: block; width: 100%; cursor: pointer;";
        statusCell.appendChild(statusBtn);
    }
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
    specialBtn.disabled = isLocked;
    specialBtn.onclick = () => tr.after(createExtraRow(dateKey));
    specialCell.appendChild(specialBtn);
    tr.appendChild(specialCell);

    // 入力セル
    tr.appendChild(createSelectCell(workTypes, dbEntry?.attendance_type, false, isLocked));
    tr.appendChild(createSelectCell(workPlaces, dbEntry?.workplace, false, isLocked));
    tr.appendChild(createSelectCell(Object.keys(shiftTimes), dbEntry?.shift, true, isLocked));
    tr.appendChild(createInputCell("time", dbEntry?.start_time?.substring(0, 5), false, isLocked));
    tr.appendChild(createInputCell("time", dbEntry?.end_time?.substring(0, 5), false, isLocked));
    tr.appendChild(createInputCell("text", dbEntry?.break_time || "0.00", true, isLocked));
    tr.appendChild(createInputCell("text", dbEntry?.overtime || "0.00", true, isLocked));
    tr.appendChild(createInputCell("text", dbEntry?.work_time || "0.00", true, isLocked));

    tableBody.appendChild(tr);
}

/**
 * 特殊行（追加行）を生成する関数
 * dbEntryを引数に追加し、メイン行と同じ列構成(11列)を保証します。
 */
export function createExtraRow(dateKey, dbEntry = {}) {
    const tr = document.createElement("tr");
    tr.setAttribute("data-date", dateKey);
    tr.setAttribute("data-extra-row", "true");

    // [0] 承認状態ボタン（メイン行と同じ構造）
    const statusCell = document.createElement("td");
    const statusBtn = document.createElement("button");
    statusBtn.className = "status-btn";
    
    const currentStatus = dbEntry?.status || "登録";
    statusBtn.textContent = currentStatus;
    statusBtn.style.cssText = "display: block; width: 100%; cursor: pointer;";
    if (currentStatus === "承認待ち") statusBtn.style.backgroundColor = "orange";

    statusBtn.onclick = async () => {
        if (window.isAdmin) {
            const form = document.createElement("form");
            form.method = "POST";
            form.action = `/admin/attendance/toggle-status/${dbEntry?.id || 0}`;
            form.innerHTML = `<input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">`;
            document.body.appendChild(form);
            form.submit();
            return;
        }
        const isNowRegistered = statusBtn.textContent === "登録";
        statusBtn.textContent = isNowRegistered ? "承認待ち" : "登録";
        statusBtn.style.backgroundColor = isNowRegistered ? "orange" : "";
        await saveData();
    };
    statusCell.appendChild(statusBtn);
    tr.appendChild(statusCell);

    // [1] 日付
    const dateCell = document.createElement("td");
    const mainRow = document.querySelector(`tr[data-date="${dateKey}"]:not([data-extra-row="true"])`);
    dateCell.textContent = mainRow ? mainRow.querySelector("td:nth-child(2)").textContent : "";
    tr.appendChild(dateCell);

    // [2] 削除ボタン
    const delCell = document.createElement("td");
    const delBtn = document.createElement("button");
    delBtn.textContent = "削除";
    delBtn.className = "special-del-btn";
    delBtn.onclick = () => { tr.remove(); saveData(); };
    delCell.appendChild(delBtn);
    tr.appendChild(delCell);

    // [3], [4], [5] 空セル（Select列用）
    for (let i = 0; i < 3; i++) tr.appendChild(document.createElement("td"));

    // [6]〜[10] 入力項目（計5セル：始業・終業・休憩・残業・労働時間）
    tr.appendChild(createInputCell("time", dbEntry?.start_time?.substring(0, 5)));
    tr.appendChild(createInputCell("time", dbEntry?.end_time?.substring(0, 5)));
    tr.appendChild(createInputCell("text", dbEntry?.break_time || "0.00", true));
    tr.appendChild(createInputCell("text", dbEntry?.overtime || "0.00", true));
    tr.appendChild(createInputCell("text", dbEntry?.work_time || "0.00", true));

    return tr;
}

export function createSelectCell(options, selectedValue, isShift, isLocked) {
    const td = document.createElement("td");
    const select = document.createElement("select");
    if (isLocked) select.disabled = true;
    select.innerHTML = `<option value="">-</option>` + options.map(v => `<option value="${v}" ${selectedValue === v ? "selected" : ""}>${v}</option>`).join("");
    if (!isLocked) {
        select.onchange = (e) => {
            const tr = select.closest("tr");
            if (isShift && shiftTimes[e.target.value]) {
                const [start, end, rest] = shiftTimes[e.target.value];
                const inputs = tr.querySelectorAll("input");
                if (inputs.length >= 3) { inputs[0].value = start; inputs[1].value = end; inputs[2].value = rest; }
            }
            calcRowTime(tr);
        };
    }
    td.appendChild(select);
    return td;
}

/**
 * 追加される行を生成する関数
 */
export function createInputCell(type, value, isReadOnly, isLocked) {
    const td = document.createElement("td");
    const input = document.createElement("input");
    input.type = type;
    input.value = value || "";
    input.style.width = "60px";
    if (isReadOnly || isLocked) {
        input.disabled = true;
        input.style.backgroundColor = "#e9ecef";
    }
    if (!isReadOnly && !isLocked) {
        input.onchange = () => { calcRowTime(input.closest("tr")); saveData(); };
    }
    td.appendChild(input);
    return td;
}

/**
 * 休暇・残業申請をサーバーへ送信する関数
 */
export function submitLeaveApplication() {
    const leaveForm = document.getElementById("leaveForm");
    const typeSelect = document.getElementById("leave_type");

    if (!leaveForm || !typeSelect) {
        alert("申請フォームが見つかりません。");
        return;
    }

    const remaining = parseFloat(window.dbRemainingPaid) || 0;
    const selectedOption = typeSelect.options[typeSelect.selectedIndex];
    const requested = parseFloat(selectedOption.getAttribute("data-days")) || 0;

    // バリデーション
    if (typeSelect.value === "有給" && requested > remaining) {
        alert(`有給残数が不足しています（残り: ${remaining}日）。`);
        return;
    }

    const targetDate = document.getElementById("leave_date")?.value;
    const type = typeSelect.value;
    const reason = document.getElementById("leave_reason")?.value;

    if (!targetDate || !type) {
        alert("対象日と申請種別を入力してください。");
        return;
    }

    // 非同期送信
    fetch("/attendance/leave-apply", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                .content,
        },
        body: JSON.stringify({
            target_date: targetDate,
            type: type,
            days_count: requested,
            reason: reason,
        }),
    })
        .then((res) =>
            res.json().then((data) => {
                if (!res.ok)
                    throw new Error(
                        data.message || "申請エラーが発生しました。",
                    );
                alert("申請が完了しました。");
                const modal = document.getElementById("leaveModal");
                if (modal) modal.style.display = "none";
                window.location.reload();
            }),
        )
        .catch((err) => {
            console.error("Leave Application Error:", err);
            alert("申請に失敗しました。\n" + err.message);
        });
}


