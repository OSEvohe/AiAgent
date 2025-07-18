document.addEventListener('DOMContentLoaded', () => {
    const chatMessages = document.getElementById('chatMessages');
    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');

    // Auto-focus on textarea
    messageInput.focus();

    // Enable/Disable send button based on input value
    messageInput.addEventListener('input', () => {
        sendButton.disabled = !messageInput.value.trim();
    });

    // Handle sending messages
    sendButton.addEventListener('click', sendMessage);
    messageInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    function sendMessage() {
        const text = messageInput.value.trim();
        if (!text) return;

        addMessage(text, 'user');
        messageInput.value = '';
        messageInput.focus();
        // Simulate AI response after a short delay
        setTimeout(() => {
            addMessage('This is an AI response.', 'ai');
        }, 1000);
    }

    function addMessage(text, sender) {
        const messageElement = document.createElement('div');
        messageElement.classList.add('message', `${sender}-message`);
        messageElement.textContent = text;
        messageElement.dataset.timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        chatMessages.appendChild(messageElement);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
});