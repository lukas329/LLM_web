<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ollama PDF Extrakcia</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
<div class="w-full max-w-2xl bg-white shadow-lg rounded-lg p-8">
    <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">📄 Ollama PDF Extrakcia</h1>

    <form action="{{ route('upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>
            <label for="pdf" class="block text-gray-700 font-semibold">Vyber PDF súbor:</label>
            <input type="file" name="pdf" required
                   class="mt-2 block w-full px-3 py-2 border rounded-lg shadow-sm focus:ring focus:ring-indigo-300">
        </div>

        <div>
            <label for="prompt" class="block text-gray-700 font-semibold">Zadajte prompt:</label>
            <textarea name="prompt" required
                      class="mt-2 block w-full px-3 py-2 border rounded-lg shadow-sm focus:ring focus:ring-indigo-300">Extrahuj kľúčové informácie zo zmluvy.</textarea>
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-indigo-700 transition">
            📤 Odoslať
        </button>
    </form>

    @if(isset($response))
        <div class="mt-6 p-4 bg-gray-50 border-l-4 border-indigo-600 rounded-lg">
            <h2 class="text-xl font-semibold text-indigo-700">✅ Odpoveď od Ollama:</h2>
            <p class="mt-2 text-gray-700">{{ $response }}</p>
        </div>
    @endif
</div>
</body>
</html>
