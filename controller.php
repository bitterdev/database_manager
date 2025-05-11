<?php

namespace Concrete\Package\DatabaseManager;

use Concrete\Core\Package\Package;
use Concrete\Core\Page\Single;
use Concrete\Core\Permission\Key\Key;
use Concrete\Core\Permission\Access\Entity\GroupEntity;
use Concrete\Core\Permission\Access\Access;
use Bitter\DatabaseManager\Provider\ServiceProvider;
use Concrete\Core\User\Group\GroupRepository;

class Controller extends Package
{
    protected $pkgHandle = 'database_manager';
    protected $pkgVersion = '1.0.1';
    protected $appVersionRequired = '9.0.0';
    protected $pkgAutoloaderRegistries = [
        'src/Bitter/DatabaseManager' => 'Bitter\DatabaseManager',
    ];

    public function getPackageDescription(): string
    {
        return t('A lightweight but powerful database manager for Concrete CMS, directly integrated into your dashboard.');
    }

    public function getPackageName(): string
    {
        return t('Database Manager');
    }

    public function on_start()
    {
        /** @var ServiceProvider $serviceProvider */
        /** @noinspection PhpUnhandledExceptionInspection */
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

        /** @var GroupRepository $repository */
        /** @noinspection PhpUnhandledExceptionInspection */
        $repository = $this->app->make(GroupRepository::class);
        $group = $repository->getGroupByID(ADMIN_GROUP_ID);

        $adminGroupEntity = GroupEntity::getOrCreate($group);

        foreach ($taskPermissions as $taskPermission) {
            $pk = Key::add('admin', $taskPermission["handle"], $taskPermission["name"], "", false, false, $pkg);

            $pa = Access::create($pk);
            $pa->addListItem($adminGroupEntity);
            $pt = $pk->getPermissionAssignmentObject();
            $pt->assignPermissionAccess($pa);
        }
    }

}
