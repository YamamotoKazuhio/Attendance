export function changeMonth(offset) {
    const titleEl = document.getElementById("calendarTitle");
    if (!titleEl) return;

    const match = titleEl.textContent.match(/(\d{4})年(\d{2})月/);
    if (!match) return;

    let y = parseInt(match[1], 10);
    let m = parseInt(match[2], 10);

    m += offset;
    if (m > 12) { m = 1; y++; } else if (m < 1) { m = 12; y--; }

    window.location.href = `/attendance?month=${y}-${String(m).padStart(2, "0")}`;
}