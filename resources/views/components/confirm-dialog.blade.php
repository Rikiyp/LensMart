@once
    <div
        x-data="{
            open: false,
            form: null,
            title: '',
            message: '',
            confirmLabel: 'Ya, lanjutkan',
            tone: 'danger',
            openDialog(detail) {
                this.form = detail.form ?? null;
                this.title = detail.title ?? 'Konfirmasi';
                this.message = detail.message ?? '';
                this.confirmLabel = detail.confirm ?? 'Ya, lanjutkan';
                this.tone = detail.tone ?? 'danger';
                this.open = true;
                this.$nextTick(() => this.$refs.cancel?.focus());
            },
            close() {
                this.open = false;
                this.form = null;
            },
            confirm() {
                const form = this.form;
                this.open = false;
                this.form = null;

                if (! form) {
                    return;
                }

                form.dataset.confirmBypass = '1';
                form.requestSubmit();
            }
        }"
        x-on:confirm-action.window="openDialog($event.detail)"
        x-on:keydown.escape.window="open && close()"
        x-effect="document.body.classList.toggle('overflow-hidden', open)"
        x-show="open"
        x-cloak
        class="fixed inset-0 z-[80] flex items-end justify-center p-4 sm:items-center"
    >
        <div class="absolute inset-0 bg-ink-950/55 backdrop-blur-[2px]" x-on:click="close()" aria-hidden="true"></div>

        <div class="relative w-full max-w-sm rounded-2xl border border-paper-300 bg-white p-5 shadow-[0_30px_80px_-30px_rgba(16,16,20,.55)]"
             role="alertdialog"
             aria-modal="true"
             :aria-label="title">
            <div class="flex items-start gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full"
                      :class="tone === 'warning' ? 'bg-amber-50 text-amber-600' : (tone === 'primary' ? 'bg-paper-100 text-ink-700' : 'bg-rose-50 text-rose-600')">
                    <x-icon name="exclamation-triangle" class="h-4 w-4" />
                </span>
                <div class="min-w-0">
                    <h2 class="text-sm font-extrabold text-ink-950" x-text="title"></h2>
                    <p class="mt-1 text-xs leading-relaxed text-ink-500" x-text="message"></p>
                </div>
            </div>

            <div class="mt-5 flex justify-end gap-2">
                <button type="button" class="btn-outline btn-sm" x-on:click="close()" x-ref="cancel">Batal</button>
                <button type="button"
                        class="btn-sm"
                        :class="tone === 'warning' ? 'btn-accent' : (tone === 'primary' ? 'btn-primary' : 'btn-danger')"
                        x-on:click="confirm()"
                        x-text="confirmLabel"></button>
            </div>
        </div>
    </div>
@endonce
