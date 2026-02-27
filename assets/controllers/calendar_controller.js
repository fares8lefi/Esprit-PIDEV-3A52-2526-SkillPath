import { Controller } from '@hotwired/stimulus';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';

export default class extends Controller {
    static targets = [];

    connect() {
        this.calendar = new Calendar(this.element, {
            plugins: [dayGridPlugin, interactionPlugin],
            initialView: 'dayGridMonth',
            events: '/fc-load-events',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,dayGridWeek'
            },
            locale: 'fr',
            eventClick: function (info) {
                if (info.event.url) {
                    window.location.href = info.event.url;
                    info.jsEvent.preventDefault();
                }
            }
        });

        this.calendar.render();
    }
}
