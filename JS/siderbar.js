const hamburger = document.querySelector(".hamburger_full");
const sidebar = document.querySelector(".sidebar");

hamburger.addEventListener("click", () => {
    sidebar.classList.toggle("collapsed");
    hamburger.classList.toggle("hamburger_half");
});

setTimeout(function () {
    var msg = document.getElementById("alert_message");
    if (msg) {
        msg.style.display = "none";
    }
}, 3000); // 3000 milliseconds = 3 seconds