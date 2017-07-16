[![Build Status](https://scrutinizer-ci.com/g/gplcart/backup/badges/build.png?b=master)](https://scrutinizer-ci.com/g/gplcart/backup/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/gplcart/backup/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/gplcart/backup/?branch=master)

Backup is a [GPL Cart](https://github.com/gplcart/gplcart) module that provides backup functionality and API for other modules.


**Installation**

1. Download and extract to `system/modules` manually or using composer `composer require gplcart/backup`. IMPORTANT: If you downloaded the module manually, be sure that the name of extracted module folder doesn't contain a branch/version suffix, e.g `-master`. Rename if needed.
2. Go to `admin/module/list` end enable the module
3. Assign backup permissions to a role `admin/user/role`

Saved backups are listed at `admin/report/backup`