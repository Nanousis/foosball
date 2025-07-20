console.log('Record Game JS Loaded');

setTimeout(() => {
    const alert = document.querySelector('.alert-success');
    if (alert) alert.remove();
}, 3000);
$(document).ready(function () {
function formatPlayer(option) {
    if (!option.id) return option.text;

    const avatarUrl = $(option.element).data('avatar');
    const playerName = option.text;

    return $(`
    <div class="d-flex align-items-center">
        <img src="${avatarUrl}" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: auto;" />
        <span class="fs-6">${playerName}</span>
    </div>
    `);
}


$('.player-select').select2({
    templateResult: formatPlayer,
    templateSelection: formatPlayer,
    width: '100%',
    placeholder: 'Select player',
    allowClear: true
});
});