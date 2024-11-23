const daysTag = document.querySelector(".days"),
    currentDate = document.querySelector(".current-date"),
    prevNextIcon = document.querySelectorAll(".icons span");

let date = new Date(),
    currYear = date.getFullYear(),
    currMonth = date.getMonth();

const months = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

const renderCalendar = (capacitaciones) => {
    let firstDayofMonth = new Date(currYear, currMonth, 1).getDay(),
        lastDateofMonth = new Date(currYear, currMonth + 1, 0).getDate(),
        lastDayofMonth = new Date(currYear, currMonth, lastDateofMonth).getDay(),
        lastDateofLastMonth = new Date(currYear, currMonth, 0).getDate();

    let liTag = "";

    // Obtener la fecha actual
    const today = new Date();
    const todayDate = today.getDate();
    const todayMonth = today.getMonth();
    const todayYear = today.getFullYear();

    // Generar los días del mes anterior
    for (let i = firstDayofMonth; i > 0; i--) {
        liTag += `<li class="inactive">${lastDateofLastMonth - i + 1}</li>`;
    }

    // Generar los días del mes actual
    for (let i = 1; i <= lastDateofMonth; i++) {
        // Verificar si es el día actual y estamos en el mes y año actual
        let isToday = i === todayDate && currMonth === todayMonth && currYear === todayYear ? "active" : "";
        let isCapacitacion = capacitaciones.includes(i) ? "event-day" : ""; // Verificar si hay una capacitación en el día actual

        // Aplicar ambas clases si corresponde
        liTag += `<li class="${isToday} ${isCapacitacion}" data-day="${i}" onclick="scrollToCapacitacion(${i})">${i}</li>`;
    }

    // Generar los días del próximo mes
    for (let i = lastDayofMonth; i < 6; i++) {
        liTag += `<li class="inactive">${i - lastDayofMonth + 1}</li>`;
    }

    currentDate.innerText = `${months[currMonth]} ${currYear}`;
    daysTag.innerHTML = liTag;
};

// Función para hacer scroll hasta la tarjeta de capacitación correspondiente
function scrollToCapacitacion(day) {
    const formattedDay = String(day).padStart(2, '0');
    const capacitacionCard = document.querySelector(`#capacitacion-${formattedDay}`);
    if (capacitacionCard) {
        capacitacionCard.scrollIntoView({ behavior: 'smooth', block: 'end' });
    }
}

const fetchCapacitacionesAndUpdateCards = (month, year) => {
    $.ajax({
        url: 'get_capacitaciones.php', // Cambia el archivo de eventos a capacitaciones
        type: 'GET',
        data: { month: month + 1, year: year },
        success: function (response) {
            const data = JSON.parse(response);
            const capacitaciones = data.events;
            const capacitacionCards = data.cards;

            renderCalendar(capacitaciones);
            $('#event-list').html(capacitacionCards); // Reemplaza el contenido de #event-list con las tarjetas de capacitación
        }
    });
}

// Inicializar el calendario
fetchCapacitacionesAndUpdateCards(currMonth, currYear);

prevNextIcon.forEach(icon => {
    icon.addEventListener("click", () => {
        currMonth = icon.id === "prev" ? currMonth - 1 : currMonth + 1;

        if (currMonth < 0 || currMonth > 11) {
            date = new Date(currYear, currMonth, new Date().getDate());
            currYear = date.getFullYear();
            currMonth = date.getMonth();
        } else {
            date = new Date();
        }
        fetchCapacitacionesAndUpdateCards(currMonth, currYear);
    });
});

setInterval(() => {
    const now = new Date();
    const currentMonth = now.getMonth();
    const currentYear = now.getFullYear();

    if (currentMonth !== currMonth || currentYear !== currYear) {
        currMonth = currentMonth;
        currYear = currentYear;
        fetchCapacitacionesAndUpdateCards(currMonth, currYear);
    }
}, 60000); // Actualización cada 60 segundos
