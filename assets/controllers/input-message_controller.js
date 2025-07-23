import {Controller} from '@hotwired/stimulus';
import {getComponent} from '@symfony/ux-live-component';


/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */


export default class extends Controller {
    static targets = ["messageInput"]

    async initialize() {
        this.component = await getComponent(this.element);
    }

    connect() {
        this.messageInputTarget.addEventListener('input', () => {
            autoResize(this.messageInputTarget);
        });

        this.messageInputTarget.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault(); // Prevent default Enter key behavior
                this.component.action('sendMessage');
            }
        });
    }
}

function autoResize(textarea) {
    textarea.style.height = 'auto';
    const newHeight = Math.min(Math.max(textarea.scrollHeight, 32), 350);
    textarea.style.height = `${newHeight}px`;
}
