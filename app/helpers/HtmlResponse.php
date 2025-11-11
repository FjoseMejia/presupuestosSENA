<?php
namespace presupuestos\helpers;

class HtmlResponse {

    public static function toast(string $message, string $type = 'info'): void
    {
        $bg = match($type) {
            'success' => 'bg-success text-white',
            'error'   => 'bg-danger text-white',
            'warning' => 'bg-warning text-dark',
            default   => 'bg-info text-white',
        };

        echo "
        <div class='toast-container position-fixed bottom-0 end-0 p-3'>
            <div class='toast align-items-center $bg border-0 show' role='alert' aria-live='assertive' aria-atomic='true'>
                <div class='d-flex'>
                    <div class='toast-body'>
                        $message
                    </div>
                    <button type='button' class='btn-close btn-close-white me-2 m-auto' data-bs-dismiss='toast' aria-label='Close'></button>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var toastElList = [].slice.call(document.querySelectorAll('.toast'))
                toastElList.map(function (toastEl) {
                    return new bootstrap.Toast(toastEl, { delay: 3000 }).show()
                })
            });
        </script>
        ";
    }
}
