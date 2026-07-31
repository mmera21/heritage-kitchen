<?php if ($errors): ?>
    <div class="error-list">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post">
    <label for="username">Username</label>
    <input id="username" name="username" type="text" value="<?php echo e($user['username']); ?>" required>

    <label for="email">Email address</label>
    <input id="email" name="email" type="email" value="<?php echo e($user['email']); ?>" required>

    <?php if (!$setup_mode): ?>
        <label for="role">Role</label>
        <select id="role" name="role">
            <option value="admin"<?php echo $user['role'] === 'admin' ? ' selected' : ''; ?>>Admin</option>
            <option value="member"<?php echo $user['role'] === 'member' ? ' selected' : ''; ?>>Member</option>
        </select>
    <?php endif; ?>

    <label for="password">Password<?php echo $password_optional ? ' (leave blank to keep current password)' : ''; ?></label>
    <input id="password" name="password" type="password"<?php echo $password_optional ? '' : ' required'; ?>>

    <label for="password_confirm">Repeat password</label>
    <input id="password_confirm" name="password_confirm" type="password"<?php echo $password_optional ? '' : ' required'; ?>>

    <div class="admin-actions">
        <button class="button" type="submit"><?php echo e($submit_label); ?></button>
        <?php if (!$setup_mode): ?>
            <a class="button secondary" href="users.php">Cancel</a>
        <?php endif; ?>
    </div>
</form>
