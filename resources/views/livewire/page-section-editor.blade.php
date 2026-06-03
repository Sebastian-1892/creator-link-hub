<div>
    @if ($open)
        <div class="fixed inset-0 z-[110] flex justify-end bg-black/40" wire:click.self="close">
            <div
                class="w-full max-w-2xl h-full bg-white shadow-2xl flex flex-col"
                wire:click.stop
                x-data="pageSectionEditor()"
                x-init="init($wire)"
                @page-section-editor-opened.window="loadContent($event.detail.content)"
            >
                <div class="border-b px-6 py-4 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('Section bearbeiten') }}</h2>
                        <p class="mt-1 text-sm text-gray-600">{{ $label !== '' ? $label : $sectionKey }}</p>
                        <p class="mt-0.5 text-xs text-gray-400 font-mono">{{ $page }} · {{ $sectionKey }} · {{ strtoupper($locale) }}</p>
                    </div>
                    <button type="button" wire:click="close" class="text-gray-400 hover:text-gray-700 text-2xl leading-none">&times;</button>
                </div>

                <div class="px-6 py-3 border-b bg-gray-50 flex flex-wrap gap-2">
                    <button type="button" class="rounded border border-gray-300 bg-white px-2 py-1 text-xs font-semibold" @click.prevent="format('bold')">B</button>
                    <button type="button" class="rounded border border-gray-300 bg-white px-2 py-1 text-xs font-semibold italic" @click.prevent="format('italic')">I</button>
                    <button type="button" class="rounded border border-gray-300 bg-white px-2 py-1 text-xs font-semibold" @click.prevent="format('h2')">H2</button>
                    <button type="button" class="rounded border border-gray-300 bg-white px-2 py-1 text-xs font-semibold" @click.prevent="format('h3')">H3</button>
                    <button type="button" class="rounded border border-gray-300 bg-white px-2 py-1 text-xs font-semibold" @click.prevent="format('link')">{{ __('Link') }}</button>
                    <button type="button" class="rounded border border-gray-300 bg-white px-2 py-1 text-xs font-semibold" @click.prevent="format('ul')">• {{ __('Liste') }}</button>
                </div>

                <div class="flex-1 overflow-y-auto p-6 space-y-4">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" wire:model="isVisible" class="rounded border-gray-300 text-indigo-600" />
                        {{ __('Section sichtbar') }}
                    </label>

                    <div
                        id="clh-page-section-editor"
                        contenteditable="true"
                        class="min-h-[320px] rounded-lg border border-gray-300 p-4 prose prose-sm max-w-none focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    ></div>

                    <x-input-error :messages="$errors->get('content')" class="mt-2" />
                </div>

                <div class="border-t px-6 py-4 flex flex-wrap gap-2 justify-end bg-gray-50">
                    <button type="button" wire:click="close" class="rounded-md px-4 py-2 text-sm text-gray-600 hover:text-gray-900">
                        {{ __('Abbrechen') }}
                    </button>
                    <button type="button" wire:click="revert" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-white">
                        {{ __('Zurücksetzen') }}
                    </button>
                    <button type="button" @click.prevent="save()" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        {{ __('Speichern') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    function pageSectionEditor() {
        return {
            wire: null,
            init(lw) {
                this.wire = lw;
            },
            loadContent(html) {
                const el = document.getElementById('clh-page-section-editor');
                if (el) {
                    el.innerHTML = html || '';
                }
            },
            format(type) {
                const el = document.getElementById('clh-page-section-editor');
                if (!el) return;
                el.focus();
                if (type === 'h2') document.execCommand('formatBlock', false, 'h2');
                else if (type === 'h3') document.execCommand('formatBlock', false, 'h3');
                else if (type === 'link') {
                    const url = prompt('URL');
                    if (url) document.execCommand('createLink', false, url);
                } else if (type === 'ul') document.execCommand('insertUnorderedList');
                else document.execCommand(type);
            },
            save() {
                const el = document.getElementById('clh-page-section-editor');
                if (!el || !this.wire) return;
                this.wire.save(el.innerHTML);
            },
        };
    }

    document.addEventListener('livewire:init', () => {
        Livewire.on('page-section-updated', (payload) => {
            const data = Array.isArray(payload) ? (payload[0] ?? {}) : (payload ?? {});
            const key = data.sectionKey;
            const html = data.html;
            if (!key || !html) return;

            const wrapper = document.getElementById(`clh-section-${key}`);
            if (!wrapper) return;

            const temp = document.createElement('div');
            temp.innerHTML = html;
            const nextSection = temp.querySelector('section');
            const currentSection = wrapper.querySelector('section');

            if (nextSection && currentSection) {
                currentSection.outerHTML = nextSection.outerHTML;
            }
        });
    });
</script>
