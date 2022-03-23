<?php
$title = 'Edit user';

$post = false;
if (!empty($data['user']))
{
    $post = true;
}

ob_start();
?>
    <div class="">
        <form id="form-rates" method="post" action="/users/edit/<?= $data['user_id'] ?>" class="g-3 login row m-auto p-auto" novalidate>
            <div class="row my-2">
                <div class="col-12 col-md-6">
                    <label for="first_name" class="form-label <?= ($post && !isset($data['errors']['first_name'])) ? 'is-valid' : 'is-invalid' ?>">First name</label>
                    <input type="text" id="first_name" name="first_name" value="<?= $data['user']['first_name'] ?? '' ?>" class="form-control text-center" required>
                    <?php if (isset($data['errors']['first_name'])): ?>
                        <div class="invalid-feedback"><?= $data['errors']['first_name'] ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-12 col-md-6">
                    <label for="last_name" class="form-label <?= ($post && !isset($data['errors']['last_name'])) ? 'is-valid' : 'is-invalid' ?>">Last name</label>
                    <input type="text" id="last_name" name="last_name" value="<?= $data['user']['last_name'] ?? '' ?>" class="form-control text-center" required>
                    <?php if (isset($data['errors']['last_name'])): ?>
                        <div class="invalid-feedback"><?= $data['errors']['last_name'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row my-2">
                <div class="col-12 col-md-6">
                    <label for="birthdate" class="form-label <?= ($post && !isset($data['errors']['birthdate'])) ? 'is-valid' : 'is-invalid' ?>">Birthdate</label>
                    <input type="date" id="birthdate" name="birthdate" value="<?= $data['user']['birthdate'] ?? '' ?>" class="form-control text-center" required>
                    <?php if (isset($data['errors']['birthdate'])): ?>
                        <div class="invalid-feedback"><?= $data['errors']['birthdate'] ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-12 col-md-6">
                    <label for="height" class="form-label <?= ($post && !isset($data['errors']['height'])) ? 'is-valid' : 'is-invalid' ?>">Height</label>
                    <input type="text" id="height" name="height" value="<?= $data['user']['height'] ?? '' ?>" class="form-control text-center" required>
                    <?php if (isset($data['errors']['height'])): ?>
                        <div class="invalid-feedback"><?= $data['errors']['height'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row my-2">
                <div class="col-12">
                    <div class=" form-check">
                        <input class="form-check-input" type="checkbox" value="true" name="club_member"
                               id="club_member" <?= (!empty($data['user']['club_member']) && $data['user']['club_member'] === 'true' ? 'checked' : '') ?>>
                        <label class="form-check-label" for="club_member">
                            Club member
                        </label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <input type="hidden" id="form_token" name="form_token" value="<?= $data['form_token']; ?>" class="form-control">
                    <input type="hidden" id="form_id" name="form_id" value="<?= $data['form_id']; ?>" class="form-control">
                </div>
            </div>

            <div class="row col-12 col-md-12 px-5 my-2">
                <button type="submit" id="get-rates" class="btn btn-primary">save</button>
            </div>
        </form>
    </div>
<?php
$output = ob_get_contents();
ob_end_clean();
