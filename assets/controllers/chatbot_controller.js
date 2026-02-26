import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['messages', 'input', 'sendButton', 'typingIndicator'];

    connect() {
        console.log('Chatbot controller connected');
    }

    async sendMessage(event) {
        event.preventDefault();

        const message = this.inputTarget.value.trim();
        if (!message) return;

        // Disable input while processing
        this.sendButtonTarget.disabled = true;
        this.inputTarget.disabled = true;

        // Add user message bubble
        this.addMessage(message, 'user');
        this.inputTarget.value = '';
        this.scrollToBottom();

        // Show typing indicator
        this.showTypingIndicator();

        try {
            const response = await fetch('/api/huggingface-chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ question: message }),
            });

            const data = await response.json();
            this.hideTypingIndicator();

            if (data.error) {
                this.addMessage('Error: ' + data.error, 'bot');
            } else {
                this.addMessage(data.answer, 'bot');
            }
        } catch (error) {
            this.hideTypingIndicator();
            this.addMessage('Sorry, I could not connect to the server. Please try again.', 'bot');
            console.error('Chatbot error:', error);
        }

        // Re-enable input
        this.sendButtonTarget.disabled = false;
        this.inputTarget.disabled = false;
        this.inputTarget.focus();
        this.scrollToBottom();
    }

    addMessage(text, sender) {
        const wrapper = document.createElement('div');
        wrapper.classList.add('flex', 'items-end', 'gap-2', 'msg-fade');

        if (sender === 'user') {
            wrapper.classList.add('justify-end');
            wrapper.innerHTML = `
                <div class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-white rounded-2xl rounded-br-md px-3 py-2 max-w-[80%] shadow-sm">
                    <p class="text-sm">${this.escapeHtml(text)}</p>
                </div>
                <div class="w-7 h-7 bg-gradient-to-r from-gray-600 to-gray-800 rounded-full flex items-center justify-center text-white text-[10px] flex-shrink-0 font-bold">You</div>
            `;
        } else {
            wrapper.innerHTML = `
                <div class="w-7 h-7 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full flex items-center justify-center text-white text-[10px] flex-shrink-0 font-bold">AI</div>
                <div class="bg-white border border-gray-200 rounded-2xl rounded-bl-md px-3 py-2 max-w-[80%] shadow-sm">
                    <p class="text-gray-700 text-sm whitespace-pre-wrap">${this.escapeHtml(text)}</p>
                </div>
            `;
        }

        // Insert before typing indicator
        this.messagesTarget.insertBefore(wrapper, this.typingIndicatorTarget);
    }

    showTypingIndicator() {
        this.typingIndicatorTarget.style.display = 'flex';
        this.scrollToBottom();
    }

    hideTypingIndicator() {
        this.typingIndicatorTarget.style.display = 'none';
    }

    scrollToBottom() {
        setTimeout(() => {
            this.messagesTarget.scrollTop = this.messagesTarget.scrollHeight;
        }, 50);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

