import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'trigger'];
    static values = {
        currentLang: { type: String, default: 'fr-FR' }
    }

    connect() {
        this.isRecording = false;
        this.recognition = null;

        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

        if (SpeechRecognition) {
            this.recognition = new SpeechRecognition();
            this.recognition.continuous = false;
            this.recognition.interimResults = false;

            this.recognition.onstart = () => this.onStart();
            this.recognition.onresult = (event) => this.onResult(event);
            this.recognition.onend = () => this.onEnd();
            this.recognition.onerror = (event) => this.onError(event);
        } else {
            console.warn("Speech recognition not supported");
        }
    }

    setLanguage(event) {
        this.currentLangValue = event.currentTarget.dataset.lang;

        // Visual feedback for language selection
        this.element.querySelectorAll('[data-lang]').forEach(el => {
            el.classList.remove('bg-primary', 'text-white');
            el.classList.add('text-slate-400');
        });
        event.currentTarget.classList.add('bg-primary', 'text-white');
        event.currentTarget.classList.remove('text-slate-400');
    }

    toggle(event) {
        event.preventDefault();

        if (!this.recognition) {
            alert("Votre navigateur ne supporte pas la reconnaissance vocale.");
            return;
        }

        if (this.isRecording) {
            this.recognition.stop();
        } else {
            try {
                this.recognition.lang = this.currentLangValue;
                this.recognition.start();
            } catch (e) {
                console.error('Recognition start error:', e);
            }
        }
    }

    onStart() {
        this.isRecording = true;
        this.triggerTarget.classList.add('bg-red-500/20', 'ring-2', 'ring-red-500', 'animate-pulse');
        this.triggerTarget.classList.remove('bg-primary/10', 'text-primary');
        this.triggerTarget.classList.add('text-red-500');
    }

    onResult(event) {
        const transcript = event.results[event.results.length - 1][0].transcript;
        if (this.hasInputTarget) {
            const currentValue = this.inputTarget.value;
            this.inputTarget.value = currentValue + (currentValue ? ' ' : '') + transcript;
            this.inputTarget.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }

    onEnd() {
        this.isRecording = false;
        this.triggerTarget.classList.remove('bg-red-500/20', 'ring-2', 'ring-red-500', 'animate-pulse', 'text-red-500');
        this.triggerTarget.classList.add('bg-primary/10', 'text-primary');
    }

    onError(event) {
        console.error('Speech recognition error:', event.error);
        if (event.error === 'network') {
            alert("Erreur réseau. Si vous utilisez Brave, activez 'Services Google pour la reconnaissance vocale' dans les paramètres.");
        } else if (event.error === 'not-allowed') {
            alert("Accès micro refusé. Vérifiez vos permissions.");
        }
        this.onEnd();
    }
}
