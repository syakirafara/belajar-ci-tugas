<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<!-- Table with stripped rows -->
<?php if (session()->getFlashData('success')) : ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session()->getFlashData('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashData('failed')) : ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= session()->getFlashData('failed') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashData('errors')) : ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            <?php foreach (session()->getFlashData('errors') as $error) : ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
    Tambah Data
</button>

<table class="table datatable">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Tanggal</th>
            <th scope="col">Nominal</th>
            <th scope="col"></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($discounts as $index => $diskon) : ?>
            <tr>
                <th scope="row"><?= $index + 1 ?></th>
                <td><?= $diskon['tanggal'] ?></td>
                <td><?= $diskon['nominal'] ?></td>
                <td>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                        data-bs-target="#editModal-<?= $diskon['id'] ?>">
                        Ubah
                    </button>
                    <a href="<?= base_url('diskon/delete/' . $diskon['id']) ?>" class="btn btn-danger"
                        onclick="return confirm('Yakin hapus data ini ?')">
                        Hapus
                    </a>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
<!-- End Table with stripped rows -->

<?= $this->include('diskon/modal_add') ?>
<?= $this->include('diskon/modal_edit') ?>

<?= $this->endSection() ?>
