{{-- resources/views/filament/components/edit-ocr.blade.php --}}
<div class="space-y-6">
    <!-- OCR Control Section -->
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="fi-section-content p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
                        Optical Character Recognition (OCR)
                    </h3>
                    <p class="text-sm text-green-600 dark:text-green-400 mt-1">
                        ✅ Gambar struk tersedia untuk diproses OCR
                    </p>
                </div>
                
                <x-filament::button
                    type="button"
                    color="primary"
                    icon="heroicon-o-sparkles"
                    id="edit-ocr-process-btn"
                    onclick="processEditOCR()"
                >
                    Proses OCR
                </x-filament::button>
            </div>
        </div>
    </div>

    <!-- Progress Section -->
    <div id="edit-ocr-progress-area" class="hidden">
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content p-6">
                <div class="fi-section-header flex items-center gap-3 mb-4">
                    <x-filament::loading-indicator class="h-5 w-5 text-primary-600" />
                    <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                        Memproses OCR...
                    </h3>
                </div>
                
                <div class="space-y-4">
                    <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                        <div id="edit-ocr-progress-bar" class="bg-primary-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <p id="edit-ocr-progress-text" class="text-sm text-gray-600 dark:text-gray-400">Memulai proses...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Section -->
    <div id="edit-ocr-results-area" class="hidden">
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content p-6">
                <div class="fi-section-header flex items-center gap-3 mb-6">
                    <svg class="h-5 w-5 text-success-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                        OCR Berhasil
                    </h3>
                </div>
                <div id="edit-ocr-stats" class="text-xs text-gray-500 dark:text-gray-400"></div>
            </div>
        </div>
    </div>
</div>

<script>
    // Global variables untuk Edit OCR
    let editOcrWorker = null;
    let editImageUrl = '{{$record->image_url}}';
    console.log('>>>', editImageUrl);

    // Function to find image on page
    function findEditImage() {
        const imagePreview = document.querySelector('.fi-fo-file-upload img') ||
                           document.querySelector('img[src*="storage"]') ||
                           document.querySelector('img[src*="livewire"]') ||
                           document.querySelector('img[src*="receipts"]');
        
        if (imagePreview && imagePreview.src) {
            editImageUrl = imagePreview.src;
            console.log('Edit image found:', editImageUrl);
            return true;
        }
        return false;
    }

    // Try to get image URL from record first, then fallback to DOM search
    @if($record && $record->image)
        editImageUrl = '{{ \Illuminate\Support\Facades\Storage::url($record->image) }}';
        console.log('Record image found:', '{{ $record->image }}');
        console.log('Storage URL:', editImageUrl);
    @else
        console.log('No record image, will search DOM...');
    @endif

    // Initialize Tesseract worker
    async function initEditOCRWorker() {
        if (!editOcrWorker) {
            console.log('Initializing Edit OCR Tesseract worker...');
            editOcrWorker = await Tesseract.createWorker('ind+eng', 1, {
                logger: m => updateEditOCRProgress(m)
            });
            console.log('Edit OCR Tesseract worker initialized');
        }
        return editOcrWorker;
    }

    function updateEditOCRProgress(m) {
        const progressBar = document.getElementById('edit-ocr-progress-bar');
        const progressText = document.getElementById('edit-ocr-progress-text');
        
        console.log('Edit OCR Progress:', m);
        
        if (m.status === 'recognizing text') {
            const progress = Math.round(m.progress * 100);
            progressBar.style.width = progress + '%';
            progressText.textContent = `Membaca teks... ${progress}%`;
        } else {
            const statusTranslations = {
                'loading tesseract core': 'Memuat Tesseract core...',
                'initializing tesseract': 'Menginisialisasi Tesseract...',
                'loading language traineddata': 'Memuat data bahasa...',
                'initializing api': 'Menyiapkan API...',
                'recognizing text': 'Mengenali teks...'
            };
            progressText.textContent = statusTranslations[m.status] || m.status;
        }
    }

    function showEditOCRResults(confidence, processTime) {
        const resultsDiv = document.getElementById('edit-ocr-results-area');
        const statsDiv = document.getElementById('edit-ocr-stats');
        
        if (statsDiv) {
            statsDiv.innerHTML = `Confidence: ${confidence}% • Processing time: ${processTime}s`;
        }
        
        if (resultsDiv) {
            resultsDiv.classList.remove('hidden');
        }
        
        console.log('Edit OCR results displayed');
    }


    // Initialize when page loads
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Edit OCR component loaded');
        console.log('Edit Image URL:', editImageUrl);
        
        // Pre-initialize worker in background
        setTimeout(() => {
            initEditOCRWorker();
        }, 1000);
    });

    async function processEditOCR() {
        // Fallback: cari gambar dari DOM jika editImageUrl tidak ada
        if (!editImageUrl) {
            console.log('editImageUrl not set, trying to find image in DOM...');
            
            const imagePreview = document.querySelector('.fi-fo-file-upload img') ||
                               document.querySelector('img[src*="storage"]') ||
                               document.querySelector('img[src*="livewire"]');
            
            if (imagePreview && imagePreview.src) {
                editImageUrl = imagePreview.src;
                console.log('Found image in DOM:', editImageUrl);
            }
        }

        if (!editImageUrl) {
            alert('Tidak ada gambar yang tersedia untuk diproses');
            console.error('No image URL found for OCR processing');
            return;
        }

        console.log('Starting Edit OCR process with image:', editImageUrl);
        const startTime = Date.now();
        
        // Show progress
        document.getElementById('edit-ocr-progress-area').classList.remove('hidden');
        document.getElementById('edit-ocr-results-area').classList.add('hidden');
        
        // Disable process button
        const processBtn = document.getElementById('edit-ocr-process-btn');
        const originalText = processBtn.innerHTML;
        processBtn.disabled = true;
        processBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Memproses...';

        try {
            // Initialize worker if needed
            await initEditOCRWorker();

            console.log('Processing image with Tesseract...', editImageUrl);
            
            // Process image
            const result = await editOcrWorker.recognize(editImageUrl);
            console.log('Edit OCR completed:', result.data);
            
            const endTime = Date.now();
            const processTime = ((endTime - startTime) / 1000).toFixed(1);
            const confidence = Math.round(result.data.confidence);

            // Update OCR result in form
            const ocrResultText = result.data.text.trim();
            
            // Find and update the textarea
            const textarea = document.querySelector('textarea[name="ocr_result"]') ||
                           document.querySelector('textarea[id*="ocr_result"]') ||
                           document.querySelector('[wire\\:model="data.ocr_result"]');
            
            if (textarea) {
                textarea.value = ocrResultText;
                // Trigger events for Livewire/Alpine
                textarea.dispatchEvent(new Event('input', { bubbles: true }));
                textarea.dispatchEvent(new Event('change', { bubbles: true }));
                console.log('Updated Edit OCR result textarea');
            } else {
                console.log('Edit OCR textarea not found');
            }

            // Update Livewire component if available
            if (window.Livewire) {
                try {
                    const component = window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));
                    if (component) {
                        component.set('data.ocr_result', ocrResultText);
                        console.log('Updated Livewire Edit OCR result');
                    }
                } catch (e) {
                    console.log('Could not update Livewire field:', e);
                }
            }

            // Show results
            showEditOCRResults(confidence, processTime);
            
        } catch (error) {
            console.error('Edit OCR Error:', error);
            alert('Terjadi error saat memproses gambar: ' + error.message);
        } finally {
            // Hide progress and re-enable button
            document.getElementById('edit-ocr-progress-area').classList.add('hidden');
            processBtn.disabled = false;
            processBtn.innerHTML = originalText;
        }
    }
</script>