document.addEventListener("DOMContentLoaded", function() {
    const flightDateInput = document.getElementById("flight_date");
    if (flightDateInput) {
        flatpickr("#flight_date", {
            dateFormat: "Y-m-d",     
            altInput: true,          
            altFormat: "d/m/Y",      
            minDate: "today"
        });
    }

    const returnDateInput = document.getElementById("return_date");
    if (returnDateInput) {
        flatpickr("#return_date", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d/m/Y",
            minDate: "today"
        });
    }
});

function searchPlaces(query, resultsId, hiddenInputId) {
    const resultsBox = document.getElementById(resultsId);
    
    if (query.length < 2) {
        resultsBox.style.display = 'none';
        return;
    }

    fetch(`ajax/search_places.php?q=${encodeURIComponent(query)}`)
        .then(response => response.text())
        .then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error("Crash: the server sent dirty data instead of clean JSON.");
                throw e;
            }
        })
        .then(data => {
            resultsBox.innerHTML = '';

            if (!data || data.error || (Array.isArray(data) && data.length === 0)) {
                resultsBox.style.display = 'none';
                return;
            }

            data.forEach(place => {
                const div = document.createElement('div');
                div.className = 'p-2 border-bottom'; 
                div.style.cursor = 'pointer';
                div.textContent = `${place.city_name} (${place.iata_code}) - ${place.airport_name}`;
                
                div.onmouseover = function() { this.style.backgroundColor = 'var(--bg-light)'; };
                div.onmouseout = function() { this.style.backgroundColor = 'white'; };
                
                div.onclick = function() {
                    document.getElementById(hiddenInputId.replace('_code', '_input')).value = place.city_name + ' (' + place.iata_code + ')';
                    document.getElementById(hiddenInputId).value = place.iata_code;
                    resultsBox.style.display = 'none';
                };
                resultsBox.appendChild(div);
            });

            resultsBox.style.display = 'block';
        })
        .catch(error => {
            console.error("Fetch error:", error);
        });
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.position-relative')) {
        const originResults = document.getElementById('origin_results');
        const destResults = document.getElementById('dest_results');
        
        if (originResults) originResults.style.display = 'none';
        if (destResults) destResults.style.display = 'none';
    }
});

function selectTrending(cityName, iataCode) {
    const destInput = document.getElementById('dest_input');
    const destCode = document.getElementById('dest_code');
    
    if (destInput && destCode) {
        destInput.value = cityName + ' (' + iataCode + ')';
        destCode.value = iataCode;
    }
}

function validateSearch() {
    const originCode = document.getElementById('origin_code');
    const destCode = document.getElementById('dest_code');

    if (originCode && destCode && (originCode.value === '' || destCode.value === '')) {
        alert("Please make sure to select an airport from the dropdown list!");
        return false;
    }
    return true;
}

function toggleEdit() {
    const displayMode = document.getElementById('display-mode');
    const editMode = document.getElementById('edit-mode');
    const actionButtons = document.getElementById('action-buttons');

    if (!displayMode || !editMode || !actionButtons) {
        return;
    }

    if (editMode.classList.contains('d-none')) {
        displayMode.classList.add('d-none');
        actionButtons.classList.add('d-none');
        editMode.classList.remove('d-none');
    } else {
        editMode.classList.add('d-none');
        displayMode.classList.remove('d-none');
        actionButtons.classList.remove('d-none');
    }
}

function toggleReturnFlight() {
    const roundTripRadio = document.getElementById('round_trip');
    const returnDateInput = document.getElementById('ret_date_input');
    const returnLabel = document.getElementById('return_label');
    const returnSection = document.getElementById('return_flight_section');
    
    if (!roundTripRadio || !returnDateInput) return;
    
    if (returnSection) {
        returnSection.classList.remove('d-none'); 
    }

    const fp = returnDateInput._flatpickr;

    if (roundTripRadio.checked) {
        returnDateInput.setAttribute('required', 'required');
        returnDateInput.disabled = false;
        returnDateInput.classList.remove('bg-light');
        returnDateInput.classList.add('bg-white');
        
        if (fp && fp.altInput) {
            fp.altInput.disabled = false;
            fp.altInput.classList.remove('bg-light');
            fp.altInput.classList.add('bg-white');
        }
        
        if (returnLabel) returnLabel.classList.remove('opacity-50');
        
    } else {
        returnDateInput.removeAttribute('required');
        returnDateInput.disabled = true;
        returnDateInput.value = '';
        returnDateInput.classList.remove('bg-white');
        returnDateInput.classList.add('bg-light');
        
        if (fp) {
            fp.clear();
            if (fp.altInput) {
                fp.altInput.disabled = true;
                fp.altInput.classList.remove('bg-white');
                fp.altInput.classList.add('bg-light');
            }
        }
        
        if (returnLabel) returnLabel.classList.add('opacity-50');
    }
}

function toggleBookingReturnFlight() {
        const roundTripRadio = document.getElementById('round_trip');
        const returnSection = document.getElementById('booking_return_section');
        const returnInput = document.getElementById('booking_ret_input');
        
        if (!returnSection) return;

        if (roundTripRadio && roundTripRadio.checked) {
            returnSection.classList.remove('d-none');
            if (returnInput && returnInput.type !== 'hidden') {
                returnInput.setAttribute('required', 'required');
            }
        } else {
            returnSection.classList.add('d-none');
            if (returnInput && returnInput.type !== 'hidden') {
                returnInput.removeAttribute('required');
            }
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        toggleBookingReturnFlight();
    });