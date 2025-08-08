function handleSelection(selection) {
    // Kirim pilihan ke backend menggunakan POST
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'menu_input_backend.php';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'selection';
    input.value = selection;

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}