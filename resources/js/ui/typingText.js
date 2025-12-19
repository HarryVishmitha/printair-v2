export function typingText({
    text,
    texts,
    speed = 80,
    deleteSpeed = 40,
    delay = 500,
    hold = 15000,
    gap = 600,
    loop = true,
} = {}) {
    const list = Array.isArray(texts) ? texts : (typeof text === 'string' ? [text] : []);

    return {
        texts: list,
        displayText: '',
        textIndex: 0,
        charIndex: 0,
        direction: 'typing', // 'typing' | 'deleting'
        _timer: null,

        start() {
            if (!this.texts.length) return;
            this.stop();
            this.displayText = '';
            this.textIndex = 0;
            this.charIndex = 0;
            this.direction = 'typing';
            this._timer = setTimeout(() => this.tick(), delay);
        },

        stop() {
            if (this._timer) {
                clearTimeout(this._timer);
                this._timer = null;
            }
        },

        currentText() {
            return this.texts[this.textIndex] ?? '';
        },

        tick() {
            const current = this.currentText();

            if (this.direction === 'typing') {
                if (this.charIndex < current.length) {
                    this.displayText += current[this.charIndex];
                    this.charIndex++;
                    this._timer = setTimeout(() => this.tick(), speed);
                    return;
                }

                // Fully typed; hold before deleting
                this.direction = 'deleting';
                this._timer = setTimeout(() => this.tick(), hold);
                return;
            }

            // deleting
            if (this.charIndex > 0) {
                this.displayText = this.displayText.slice(0, -1);
                this.charIndex--;
                this._timer = setTimeout(() => this.tick(), deleteSpeed);
                return;
            }

            // Fully deleted; move to next text
            if (!loop && this.textIndex >= this.texts.length - 1) {
                this.stop();
                return;
            }

            this.textIndex = (this.textIndex + 1) % this.texts.length;
            this.direction = 'typing';
            this._timer = setTimeout(() => this.tick(), gap);
        },
    };
}
