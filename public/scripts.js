document.addEventListener('DOMContentLoaded', () => {
    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');
    const chatMessages = document.getElementById('chatMessages');
    const typingIndicator = document.getElementById('typingIndicator');

    messageInput.addEventListener('input', () => {
        sendButton.disabled = !messageInput.value.trim();
        autoResize(messageInput);
    });

    messageInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    function autoResize(textarea) {
        textarea.style.height = 'auto';
        const newHeight = Math.min(Math.max(textarea.scrollHeight, 32), 160);
        textarea.style.height = `${newHeight}px`;
    }

    function sendMessage() {
        if (!messageInput.value.trim()) return;
        addMessage(messageInput.value, 'user');
        messageInput.value = '';
        typingIndicator.style.display = 'flex';
        setTimeout(() => {
            addMessage('This is an AI response', 'ai');
            chatMessages.scrollTop = chatMessages.scrollHeight;
            typingIndicator.style.display = 'none';
        }, 1000);
    }

    function addMessage(text, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}-message`;
        messageDiv.textContent = text;
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    sendButton.addEventListener('click', sendMessage);
    messageInput.focus();
});