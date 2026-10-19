<?php
// the "are you sure?" page shown before deleting or cancelling
// set these before including: $title, $question, $yes (link for Yes), $no (link for No), $danger (true = delete, false = cancel)
include __DIR__ . "/header.php";
?>

<section class="confirm <?php echo $danger ? "danger" : "caution"; ?>" role="alertdialog" aria-labelledby="confirm-title" aria-describedby="confirm-text">
    <span class="confirm-icon" aria-hidden="true">
        <?php if ($danger) { ?>
            <svg viewBox="0 0 24 24"><path d="M4.5 7h15M9.5 7V5h5v2M6.5 7l1 12.5h9l1-12.5M10.5 11v5M13.5 11v5"/></svg>
        <?php } else { ?>
            <svg viewBox="0 0 24 24"><path d="M5 9.5h14M8.5 3.5v3M15.5 3.5v3M6 6h12a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1ZM10 13l4 4M14 13l-4 4"/></svg>
        <?php } ?>
    </span>
    <h2 id="confirm-title"><?php echo $title; ?></h2>
    <p id="confirm-text"><?php echo $question; ?></p>
    <div class="confirm-actions">
        <a class="confirm-yes" href="<?php echo $yes; ?>">Yes</a>
        <a class="confirm-no" href="<?php echo $no; ?>">No</a>
    </div>
    <small class="confirm-hint">Press Esc to go back</small>
</section>

<?php include __DIR__ . "/footer.php"; ?>
