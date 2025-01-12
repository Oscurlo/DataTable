<?php

use Oscurlo\DataTable\Response;

include dirname(__DIR__) . "/vendor/autoload.php";

Response::code(200)::html(<<<HTML
<link href="https://cdn.datatables.net/v/bs5/dt-2.2.1/datatables.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

<style>
    .center {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }
</style>

<div class="center container">
    <div class="card w-100">
        <div class="card-header">
            <h2 class="card-title">Orders</h2>
        </div>
        <div class="card-body">
            <div class="">
                <table id="table-serverside" class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Product name</th>
                            <th>Order date</th>
                            <th>#</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<script defer src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script defer src="https://cdn.datatables.net/v/bs5/dt-2.2.1/datatables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<script type="module">
    $(document).ready(() => {
        $('#table-serverside').DataTable( {
            serverSide: true,
            responsive: true,
            ajax: './back.php?action=orders'
        } );
    })
</script>
HTML);
