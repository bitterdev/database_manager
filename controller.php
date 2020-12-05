<?php

/**
 * @project:   Database Manager
 *
 * @author     Fabian Bitter (fabian@bitter.de)
 * @copyright  (C) 2020 Fabian Bitter (www.bitter.de)
 * @version    X.X.X
 */

namespace Concrete\Package\DatabaseManager;

use Concrete\Core\Package\Package;
use Concrete\Core\Page\Single;
use Concrete\Core\Permission\Key\Key;
use Concrete\Core\User\Group\Group;
use Concrete\Core\Permission\Access\Entity\GroupEntity;
use Concrete\Core\Permission\Access\Access;
use Bitter\DatabaseManager\Provider\ServiceProvider;

class Controller extends Package
{

    protected $pkgHandle = 'database_manager';
    protected $pkgVersion = '1.0.0';
    protected $appVersionRequired = '8.0.0';
    protected $pkgAutoloaderRegistries = [
        'src/Bitter/DatabaseManager' => 'Bitter\DatabaseManager',
    ];

    public function getPackageDescription()
    {
        return t('Database management integrated in concrete5 dashboard.');
    }

    public function getPackageName()
    {
        return t('Database Manager');
    }

    public function on_start()
    {
        /** @var ServiceProvider $serviceProvider */
        $serviceProvider = $this->app->make(ServiceProvider::class);
        $serviceProvider->register();
    }

    public function install()
    {
        $pkg = parent::install();

        /*
         * Install the dashboard page
         */

        Single::add("/dashboard/database_manager", $pkg);


        /*
         * Install the task permissions
         */

        $taskPermissions = [
            [
                "handle" => "access_database_manager",
                "name" => t("Access Database Manager")
            ],

            [
                "handle" => "insert_rows",
                "name" => t("Insert Rows")
            ],

            [
                "handle" => "edit_rows",
                "name" => t("Edit Rows")
            ],

            [
                "handle" => "delete_rows",
                "name" => t("Delete Rows")
            ]
        ];

        $group = Group::getByID(ADMIN_GROUP_ID);

        $adminGroupEntity = GroupEntity::getOrCreate($group);

        foreach ($taskPermissions as $taskPermission) {
            /** @var Key $pk */
            $pk = Key::add('admin', $taskPermission["handle"], $taskPermission["name"], "", false, false, $pkg);

            $pa = Access::create($pk);
            /** @noinspection PhpParamsInspection */
            $pa->addListItem($adminGroupEntity);
            $pt = $pk->getPermissionAssignmentObject();
            $pt->assignPermissionAccess($pa);
        }
    }

}
