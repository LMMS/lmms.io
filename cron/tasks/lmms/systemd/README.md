This directory contains systemd units, they need to be placed in `/etc/systemd/system/`. After that, a `systemctl daemon-reload` should be issued.
To enable i18n automatic compilation, place the files to aforementioned directory, and issue (as root):

```bash
systemctl start php-watcher.path
systemctl enable php-watcher.path
```

If you want to take a look at the status of the units,issue:

```bash
systemctl status php-watcher.path
systemctl status php-watcher.service
```

The `php-watcher.service` should report `(code=exited, status=0/SUCCESS)` and `php-watcher.path` should report `active (waiting)`
