import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'trigger'];
    static values = {
        currentLang: { type: String, default: 'fr-FR' }
    }

    connect() {
        this.isRecording = false;
        this.recognition = null;

        // More robust API detection
        const SpeechRecognition = window.SpeechRecognition || 
                                window.webkitSpeechRecognition || 
                                window.mozSpeechRecognition || 
                                window.msSpeechRecognition;

        if (SpeechRecognition) {
            console.log("Voice Recognition: API detected successfully.");
            this.recognition = new SpeechRecognition();
            
            // Set to true to keep listening even after pauses
            this.recognition.continuous = true; 
            
            // Show results as you speak
            this.recognition.interimResults = true; 
            
            this.recognition.onstart = () => this.onStart();
            this.recognition.onresult = (event) => this.onResult(event);
            this.recognition.onend = () => this.onEnd();
            this.recognition.onerror = (event) => this.onError(event);
        } else {
            console.error("Voice Recognition: Web Speech API is not supported in this browser.");
            this.triggerTarget.title = "Non supporté par votre navigateur";
            this.triggerTarget.classList.add('opacity-30', 'cursor-not-allowed');
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
            alert("Votre navigateur ne supporte pas la reconnaissance vocale. Utilisez Chrome, Edge ou Brave.");
            return;
        }

        if (this.isRecording) {
            console.log("Voice Recognition: Stopping...");
            this.recognition.stop();
        } else {
            try {
                // Ensure we use the latest selected language
                this.recognition.lang = this.currentLangValue;
                console.log("Voice Recognition: Starting with language:", this.recognition.lang);
                
                // Clear any previous session state
                this.tempTranscript = '';
                this.recognition.start();
            } catch (e) {
                console.error('Voice Recognition Start Error:', e);
                // If already started, just reset visual state
                if (e.name === 'InvalidStateError') {
                    this.recognition.stop();
                    setTimeout(() => this.recognition.start(), 200);
                }
            }
        }
    }

    onStart() {
        this.isRecording = true;
        this.triggerTarget.classList.add('bg-red-500/20', 'ring-2', 'ring-red-500', 'animate-pulse');
        this.triggerTarget.classList.remove('bg-primary/10', 'text-primary');
        this.triggerTarget.classList.add('text-red-500');
        
        const dot = document.getElementById('mic-status');
        if (dot) dot.classList.remove('hidden');
    }

    onResult(event) {
        let finalTranscript = '';
        let interimTranscript = '';

        for (let i = event.resultIndex; i < event.results.length; ++i) {
            if (event.results[i].isFinal) {
                finalTranscript += event.results[i][0].transcript;
            } else {
                interimTranscript += event.results[i][0].transcript;
            }
        }

        if (finalTranscript && this.hasInputTarget) {
            const currentValue = this.inputTarget.value;
            this.inputTarget.value = currentValue + (currentValue && !currentValue.endsWith(' ') ? ' ' : '') + finalTranscript;
            
            // Auto-scroll textarea if it's too long
            this.inputTarget.scrollTop = this.inputTarget.scrollHeight;
            
            // Trigger input event for any listeners
            this.inputTarget.dispatchEvent(new Event('input', { bubbles: true }));
        }
        
        // You could show interimTranscript in a small label if you wanted live feedback
        console.log("Transcript:", finalTranscript || interimTranscript);
    }

    onEnd() {
        this.isRecording = false;
        this.triggerTarget.classList.remove('bg-red-500/20', 'ring-2', 'ring-red-500', 'animate-pulse', 'text-red-500');
        this.triggerTarget.classList.add('bg-primary/10', 'text-primary');

        const dot = document.getElementById('mic-status');
        if (dot) dot.classList.add('hidden');
    }

    onError(event) {
        console.error('Speech recognition error:', event.error);
        if (event.error === 'network') {
            alert("Erreur réseau. Si vous utilisez Brave, activez 'Services Google pour la reconnaissance vocale' dans les paramètres.");
        } else if (event.error === 'not-allowed') {
            if (window.location.hostname === '127.0.0.1') {
                alert("Accès refusé. Essayez d'utiliser l'adresse http://localhost:8000 au lieu de http://127.0.0.1:8000 pour que le navigateur autorise le micro.");
            } else {
                alert("Accès micro refusé. Vérifiez vos permissions dans les paramètres du navigateur.");
            }
        }
        this.onEnd();
    }
}
