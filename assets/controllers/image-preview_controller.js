import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.element.addEventListener('change', this.handleFileSelect.bind(this));
    }

    handleFileSelect(event) {
        const files = event.target.files;
        const previewContainer = document.createElement('div');
        previewContainer.className = 'mt-4 grid grid-cols-4 gap-4';
        
        Array.from(files).forEach(file => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                const previewDiv = document.createElement('div');
                previewDiv.className = 'relative border border-gray-200 rounded-lg overflow-hidden';
                previewDiv.style.paddingBottom = '100%';
                
                reader.onload = (e) => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'absolute inset-0 w-full h-full object-cover';
                    previewDiv.appendChild(img);
                };
                
                reader.readAsDataURL(file);
                previewContainer.appendChild(previewDiv);
            }
        });

        // Supprimer l'ancien conteneur de prévisualisation s'il existe
        const existingPreview = this.element.nextElementSibling;
        if (existingPreview && existingPreview.classList.contains('grid')) {
            existingPreview.remove();
        }

        // Ajouter le nouveau conteneur de prévisualisation
        if (previewContainer.children.length > 0) {
            this.element.parentNode.insertBefore(previewContainer, this.element.nextSibling);
        }
    }
} 