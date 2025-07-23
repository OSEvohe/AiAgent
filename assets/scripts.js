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
        if (e.key === 'Enter' && e.shiftKey) {
           sendButton.click();
        }
    });

    function autoResize(textarea) {
        textarea.style.height = 'auto';
        const newHeight = Math.min(Math.max(textarea.scrollHeight, 32), 160);
        textarea.style.height = `${newHeight}px`;
    }

    messageInput.focus();
});