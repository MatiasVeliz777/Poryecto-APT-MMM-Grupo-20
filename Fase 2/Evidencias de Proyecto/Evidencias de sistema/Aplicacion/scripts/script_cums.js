const daysTag = document.querySelector(".days"),
    currentDate = document.querySelector(".current-date"),
    prevNextIcon = document.querySelectorAll(".icons span");

let date = new Date(),
    currYear = date.getFullYear(),
    currMonth = date.getMonth();

const months = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

const renderCalendar = (events) => {
    let firstDayofMonth = new Date(currYear, currMonth, 1).getDay(),
        lastDateofMonth = new Date(currYear, currMonth + 1, 0).getDate(),
        lastDayofMonth = new Date(currYear, currMonth, lastDateofMonth).getDay(),
        lastDateofLastMonth = new Date(currYear, currMonth, 0).getDate();

    let liTag = "";

    // Obtener la fecha actual en cada renderizado
    const today = new Date();  // Obtener siempre la fecha actual del sistema
    const todayDate = today.getDate(); // Día del mes
    const todayMonth = today.getMonth(); // Mes actual
    const todayYear = today.getFullYear(); // Año actual

    // Generar los días de la semana anterior al mes actual
    for (let i = firstDayofMonth; i > 0; i--) {
        liTag += `<li class="inactive">${lastDateofLastMonth - i + 1}</li>`;
    }

    // Generar los días del mes actual
    for (let i = 1; i <= lastDateofMonth; i++) {
        // Verificar si es el día actual y estamos en el mes y año actual
        let isToday = i === todayDate && currMonth === todayMonth && currYear === todayYear ? "active" : "";
        let isEvent = events.includes(i) ? "event-day" : ""; // Verificar si hay un evento

        // Aplicar ambas clases si corresponde
        liTag += `<li class="${isToday} ${isEvent}" data-day="${i}" onclick="scrollToEvent(${i})">${i}</li>`;
    }

    // Generar los días del próximo mes que ocupan espacio en la última fila del calendario
    for (let i = lastDayofMonth; i < 6; i++) {
        liTag += `<li class="inactive">${i - lastDayofMonth + 1}</li>`;
    }

    // Actualizar la fecha actual en el encabezado del calendario
    currentDate.innerText = `${months[currMonth]} ${currYear}`;
    daysTag.innerHTML = liTag;
};

// Función para hacer scroll hasta la tarjeta de evento correspondiente
function scrollToEvent(day) {
    const formattedDay = String(day).padStart(2, '0');
    const eventCard = document.querySelector(`#event-${formattedDay}`);
    if (eventCard) {
        eventCard.scrollIntoView({ behavior: 'smooth', block: 'end' });
    }
}


// Llamada AJAX para obtener cumpleaños
const fetchBirthdaysAndUpdateCards = (month, year) => {
    $.ajax({
        url: 'get_birthdays.php',
        type: 'GET',
        data: { month: month + 1, year: year }, // Mes indexado en 0 en JS
        success: function (response) {
            const data = JSON.parse(response);
            const birthdays = data.birthdays;
            const birthdayCards = data.cards;

            renderCalendar(birthdays);  // Actualizar el calendario
            $('#birthday-list').html(birthdayCards);  // Actualizar las tarjetas
        }
    });
}

// Inicializar el calendario y las tarjetas
fetchBirthdaysAndUpdateCards(currMonth, currYear);

// Controlar el cambio de mes con los iconos de navegación
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
        fetchBirthdaysAndUpdateCards(currMonth, currYear);
    });
});

// Actualizar el calendario cada 60 segundos para reflejar cambios en la fecha del sistema
setInterval(() => {
    const now = new Date();
    const currentMonth = now.getMonth();
    const currentYear = now.getFullYear();

    // Solo actualizar si el mes o año han cambiado
    if (currentMonth !== currMonth || currentYear !== currYear) {
        currMonth = currentMonth;
        currYear = currentYear;
        fetchBirthdaysAndUpdateCards(currMonth, currYear); // Forzar la actualización del calendario
    }
}, 60000); // Actualización cada 60 segundos
