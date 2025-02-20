function Task() {
    document.getElementById("taskModal").style.display = "none";
}

Task();

function openTaskModal() {
    let modal = document.getElementById("taskModal");
    if (modal) {
        modal.style.display = "flex";
    }
}

function closeTaskModal() {
    let modal = document.getElementById("taskModal");
    if (modal) {
        modal.style.display = "none";
    }
}

// Zatvori modal na ESC
document.addEventListener("keydown", function (event) {
    let modal = document.getElementById("taskModal");
    if (modal && event.key === "Escape" && modal.style.display === "flex") {
        closeTaskModal();
    }
});

// Zatvori modal klikom van njega
window.addEventListener("click", function (event) {
    let modal = document.getElementById("taskModal");
    if (modal && event.target === modal) {
        closeTaskModal();
    }
});

// Učitavanje zadataka
function loadUserTasks() {
    fetch("get_tasks.php")
        .then(response => response.json())
        .then(tasks => {
            console.log("Učitani zadaci:", tasks);
            let taskContainer = document.getElementById("taskList");

            taskContainer.innerHTML = ""; // Reset liste

            if (tasks.error) {
                taskContainer.innerHTML = `<p>${tasks.error}</p>`;
                return;
            }

            // Ako nema zadataka, prikaži poruku
            if (tasks.length === 0) {
                taskContainer.innerHTML = `<p>Nema dodeljenih zadataka.</p>`;
                return;
            }

            // Prikaz zadataka
            tasks.forEach(task => {
                let taskItem = document.createElement("li");
                taskItem.innerHTML = `
                    <strong>${task.title}</strong>: ${task.description} <br> 
                    <span style="color: ${task.status === 'pending' ? 'orange' : 'green'}">
                        Status: ${task.status}
                    </span>`;
                taskContainer.appendChild(taskItem);
            });
        })
        .catch(error => console.error("Greška pri učitavanju zadataka:", error));
}

// Učitaj zadatke kad se stranica učita
document.addEventListener("DOMContentLoaded", loadUserTasks);