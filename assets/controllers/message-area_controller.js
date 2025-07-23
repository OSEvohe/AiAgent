import {Controller} from '@hotwired/stimulus';
import {getComponent} from '@symfony/ux-live-component';
import hljs from 'highlight.js';
import 'highlight.js/styles/atom-one-dark.css';


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
    static targets = ["messageArea", "scrollToBottomButton"];

    async initialize() {
        this.component = await getComponent(this.element);

        this.component.on('render:finished', (component) => {
            highlightCode();

            if (this.messageNumbers !== this.messageAreaTarget.querySelectorAll('.message').length && this.scrollToBottomButtonTarget.classList.contains('d-none')) {
                // if the number of messages has changed, scroll to the bottom
                this.messageNumbers = this.messageAreaTarget.querySelectorAll('.message').length;
                console.log('Message count changed, scrolling to bottom: ' + this.messageNumbers);
                this.scrollToBottom(true);
            }
        });


    }

    // count of messages in the message area
    messageNumbers = this.messageAreaTarget.querySelectorAll('.message').length;


    connect() {
        highlightCode();

        // at connect we scroll to the bottom of the message area
        this.scrollToBottom(false);
        // and ensure button scrool is hidden
        displayScrollToBottomButton(this.messageAreaTarget, this.scrollToBottomButtonTarget);

    }

    scrolling() {
        // display or hide the scroll to bottom button based on the scroll position
        displayScrollToBottomButton(this.messageAreaTarget, this.scrollToBottomButtonTarget);
    }

    scrollToBottom(smooth = true) {
        // Scroll to the bottom of the message container in a smooth way
        this.messageAreaTarget.scrollTo({
            top: this.messageAreaTarget.scrollHeight,
            behavior: smooth ? 'smooth' : 'auto'
        });
    }
}

function displayScrollToBottomButton(messageContainer, button) {
    if (isAtBottom(messageContainer)) {
        button.classList.add('d-none');
    } else {
        button.classList.remove('d-none');
    }
}

function isAtBottom(messageContainer) {
    return messageContainer.scrollTop + messageContainer.clientHeight >= messageContainer.scrollHeight;
}

function highlightCode() {
    // Highlight all code blocks in the message area
    const codeBlocks = document.querySelectorAll('.message pre code');
    codeBlocks.forEach((block) => {
        // seulement si n'a pas data-highlighted
        if (!block.hasAttribute('data-highlighted')) {
            hljs.highlightElement(block);
        }
    });
}
