<x-layouts.admin title="Import Word">
    <a href="{{ route('admin.courses.index') }}" class="text-sm text-indigo-700">← Cours</a>
    <h1 class="mb-2 mt-3 text-2xl font-semibold">Importer un cours Word</h1>
    <p class="mb-6 text-slate-600">
        Le fichier <strong>.docx</strong> est découpé automatiquement en cours, chapitres et interrogations.
    </p>

    <a href="{{ route('admin.courses.import.template') }}"
       class="mb-8 inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700">
        Télécharger le modèle Word
    </a>

    <div class="mb-8 rounded-xl border bg-white p-6 text-sm leading-relaxed text-slate-700">
        <p class="font-medium text-slate-900">Structure attendue dans Word</p>
        <p class="mt-2">
            Commencez par le modèle : il contient déjà les Titres 1, 2 et 3, un exemple de QCM
            (<code>*</code> ou <code>(juste)</code>) et une question ouverte (<code>Réponse:</code>).
            La mise en forme Word du chapitre (gras, italique, souligné, listes, tableaux, images, couleurs, alignement) est conservée à l’import.
        </p>
        <ul class="mt-3 list-disc space-y-1 ps-5">
            <li><strong>Titre 1</strong> : nom du cours</li>
            <li>Paragraphes suivants (avant le premier Titre 2) : description du cours</li>
            <li><strong>Titre 2</strong> : un chapitre</li>
            <li>Le texte sous le Titre 2 : contenu du chapitre</li>
            <li><strong>Titre 3</strong> contenant « Interro », « Quiz » ou « QCM » : démarre l’interrogation</li>
            <li>Questions numérotées : <code>1. …</code> ou <code>Question 1 …</code></li>
            <li>QCM : <code>a) …</code> <code>b) …</code> — marquez la bonne réponse avec <code>*</code> ou <code>(juste)</code></li>
            <li>Question ouverte : <code>Réponse: …</code></li>
        </ul>
    </div>

    <form method="POST" action="{{ route('admin.courses.import.store') }}" enctype="multipart/form-data"
          class="w-full max-w-lg rounded-xl border bg-white p-4 sm:p-6">
        @csrf

        <x-academic-cascade
            :faculties="$faculties"
            :options-json="$optionsJson"
            :promotions="$promotions"
            :selected-faculty="old('faculty_id', auth()->user()->faculty_id)"
            :selected-option="old('option_id')"
            :selected-promotion="old('promotion_id')"
            :lock-faculty="auth()->user()->isAdmin() && (bool) auth()->user()->faculty_id"
        />

        <label class="mb-2 block text-sm">Fichier Word (.docx)</label>
        <input type="file" name="document" accept=".docx,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
               required class="mb-2 w-full rounded-lg border px-3 py-2">
        <p class="mb-4 text-xs text-slate-500">Taille maximale : {{ $maxUploadLabel }}. Compressez les images dans Word si le fichier est plus lourd.</p>

        <label class="mb-6 flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_published" value="1" @checked(old('is_published', true))>
            Publier immédiatement
        </label>

        @if ($errors->any())
            <ul class="mb-4 list-disc ps-5 text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <button class="min-h-12 w-full rounded-xl bg-indigo-600 px-4 py-3 text-white sm:w-auto">Importer et découper</button>
    </form>
</x-layouts.admin>
