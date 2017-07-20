<?php

use Alsofronie\Uuid\UuidModelTrait;
use Alsofronie\Uuid\Uuid32ModelTrait;
use Alsofronie\Uuid\UuidBinaryModelTrait;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model as Eloquent;

class EloquentUuidTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests the creation of model with uuid as primary key
     *
     * @return void
     */
    public function testCreation()
    {
        $creation = EloquentUserModel::create([
            'username'=>'alsofronie',
            'password'=>'secret'
        ]);

        static::assertEquals(36, strlen($creation->id));

        $model = EloquentUserModel::first();

        static::assertEquals(36, strlen($model->id));
        static::assertRegExp('/^[0-9a-f-]{36}$/', $model->id);
        static::assertRegExp('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $model->id);

        static::assertEquals($creation->id, $model->id);
    }

    public function test32Creation()
    {
        $creation = Eloquent32UserModel::create([
            'username'=>'alsofronie',
            'password'=>'secret'
        ]);

        static::assertEquals(32, strlen($creation->id));

        $model = Eloquent32UserModel::first();

        static::assertEquals(32, strlen($model->id));
        static::assertRegExp('/^[0-9a-f]{32}$/', $model->id);
        static::assertRegExp('/^[0-9a-f]{32}$/', $model->id);

        static::assertEquals($creation->id, $model->id);
    }

    public function testBinaryCreation()
    {
        $creation = EloquentBinUserModel::create([
            'username'=>'alsofronie-binary',
            'password'=>'secret'
        ]);

        $model = EloquentBinUserModel::first();

        $binUuid = $model->id;

        // We should be good with strlen because
        // in PHP the strings are not delimited by \0 like in C
        // but they are storing the length, also
        static::assertEquals(16, strlen($binUuid));

        static::assertEquals($creation->id, $model->id);

        $hexUuid = bin2hex($binUuid);

        // This is to be expected, but just to show...
        static::assertEquals(32, strlen($hexUuid));
        static::assertEquals($hexUuid, $model->id_string);
    }

    public function testBinaryOptimizedCreation()
    {
        $creation = EloquentBinOptimizedUserModel::create([
            'username'=>'alsofronie-binary',
            'password'=>'secret'
        ]);

        $model = EloquentBinOptimizedUserModel::first();

        $binUuid = $model->id;

        // We should be good with strlen because
        // in PHP the strings are not delimited by \0 like in C
        // but they are storing the length, also
        static::assertEquals(16, strlen($binUuid));

        static::assertEquals($creation->id, $model->id);

        $hexUuid = EloquentBinOptimizedUserModel::toNormal($binUuid);

        // This is to be expected, but just to show...
        static::assertEquals(32, strlen($hexUuid));
        static::assertEquals($hexUuid, $model->id_string);
    }

    public function testBinaryFind()
    {
        $creation = EloquentBinUserModel::create([
            'username'=>'alsofronie-binary',
            'password'=>'secret'
        ]);

        $model = EloquentBinUserModel::first();

        $binUuid = $model->id;
        $hexUuid = bin2hex($binUuid);

        static::assertEquals($hexUuid, $model->id_string);

        $found = EloquentBinUserModel::find($binUuid);
        static::assertEquals($found, $model);
    }

    public function testBinaryFindOrFail()
    {
        $creation = EloquentBinUserModel::create([
            'username'=>'alsofronie-binary',
            'password'=>'secret'
        ]);

        $model = EloquentBinUserModel::first();

        $binUuid = $model->id;
        $hexUuid = bin2hex($binUuid);

        static::assertEquals($hexUuid, $model->id_string);

        $found = EloquentBinUserModel::findOrFail($binUuid);
        static::assertEquals($found, $model);
    }

    public function testBinaryOptimizedFind()
    {
        $creation = EloquentBinOptimizedUserModel::create([
            'username'=>'alsofronie-binary',
            'password'=>'secret'
        ]);

        $model = EloquentBinOptimizedUserModel::first();

        $binUuid = $model->id;
        $hexUuid = EloquentBinOptimizedUserModel::toNormal($binUuid);

        static::assertEquals($hexUuid, $model->id_string);

        $found = EloquentBinOptimizedUserModel::find($binUuid);
        static::assertEquals($found, $model);
    }

    public function testBinaryOptimizedFindOrFail()
    {
        $creation = EloquentBinOptimizedUserModel::create([
            'username'=>'alsofronie-binary',
            'password'=>'secret'
        ]);

        $model = EloquentBinOptimizedUserModel::first();

        $binUuid = $model->id;
        $hexUuid = EloquentBinOptimizedUserModel::toNormal($binUuid);

        static::assertEquals($hexUuid, $model->id_string);

        $found = EloquentBinOptimizedUserModel::findOrFail($binUuid);
        static::assertEquals($found, $model);
    }

    public function testBinaryFindFromStringUuid()
    {
        $creation = EloquentBinUserModel::create([
            'username'=>'alsofronie-binary',
            'password'=>'secret'
        ]);

        $model = EloquentBinUserModel::first();

        $binUuid = $model->id;
        $hexUuid = bin2hex($binUuid);

        static::assertEquals($hexUuid, $model->id_string);

        $found = EloquentBinUserModel::find($hexUuid);
        static::assertEquals($found, $model);
    }

    public function testBinaryFindOrFailFromStringUuid()
    {
        $creation = EloquentBinUserModel::create([
            'username'=>'alsofronie-binary',
            'password'=>'secret'
        ]);

        $model = EloquentBinUserModel::first();

        $binUuid = $model->id;
        $hexUuid = bin2hex($binUuid);

        static::assertEquals($hexUuid, $model->id_string);

        $found = EloquentBinUserModel::findOrFail($hexUuid);
        static::assertEquals($found, $model);
    }

    public function testBinaryOptimizedFindFromStringUuid()
    {
        $creation = EloquentBinOptimizedUserModel::create([
            'username'=>'alsofronie-binary',
            'password'=>'secret'
        ]);

        $model = EloquentBinOptimizedUserModel::first();

        $binUuid = $model->id;
        $hexUuid = EloquentBinOptimizedUserModel::toNormal($binUuid);

        static::assertEquals($hexUuid, $model->id_string);

        $found = EloquentBinOptimizedUserModel::find($hexUuid);
        static::assertEquals($found, $model);
    }

    public function testBinaryOptimizedFindOrFailFromStringUuid()
    {
        $creation = EloquentBinOptimizedUserModel::create([
            'username'=>'alsofronie-binary',
            'password'=>'secret'
        ]);

        $model = EloquentBinOptimizedUserModel::first();

        $binUuid = $model->id;
        $hexUuid = EloquentBinOptimizedUserModel::toNormal($binUuid);

        static::assertEquals($hexUuid, $model->id_string);

        $found = EloquentBinOptimizedUserModel::findOrFail($hexUuid);
        static::assertEquals($found, $model);
    }

    public function testRelationshipWithStringUuid()
    {
        $firstUser = EloquentUserModel::create([
            'username'=>'first-user',
            'password'=>'secret'
        ]);

        $secondUser = EloquentUserModel::create([
            'username'=>'second-user',
            'password'=>'secret'
        ]);

        $postsForFirstUser = [];
        $postsForSecondUser = [];

        for ($i=0; $i < 10; $i++) {
            $postsForFirstUser[] = new EloquentPostModel([
                'name'=>'First user - post ' . $i,
            ]);

            $postsForSecondUser[] = EloquentPostModel::create([
                'name'=>'Second user - post ' . $i,
                'user_id'=>$secondUser->id,
            ]);
        }

        $firstUser->posts()->saveMany($postsForFirstUser);

        static::assertEquals(10, $firstUser->posts()->count());
        static::assertEquals(10, $secondUser->posts()->count());
    }

    public function testRelationshipWith32Uuid()
    {
        $firstUser = Eloquent32UserModel::create([
            'username'=>'first-user',
            'password'=>'secret'
        ]);

        $secondUser = Eloquent32UserModel::create([
            'username'=>'second-user',
            'password'=>'secret'
        ]);

        $postsForFirstUser = [];
        $postsForSecondUser = [];

        for ($i=0; $i < 10; $i++) {
            $postsForFirstUser[] = new Eloquent32PostModel([
                'name'=>'First user - post ' . $i,
            ]);

            $postsForSecondUser[] = Eloquent32PostModel::create([
                'name'=>'Second user - post ' . $i,
                'user_id'=>$secondUser->id,
            ]);
        }

        $firstUser->posts()->saveMany($postsForFirstUser);

        static::assertEquals(10, $firstUser->posts()->count());
        static::assertEquals(10, $secondUser->posts()->count());
    }

    public function testRelationshipWithBinUuid()
    {
        $firstUser = EloquentBinUserModel::create([
            'username'=>'first-user',
            'password'=>'secret'
        ]);

        $secondUser = EloquentBinUserModel::create([
            'username'=>'second-user',
            'password'=>'secret'
        ]);

        $postsForFirstUser = [];
        $postsForSecondUser = [];

        for ($i=0; $i < 10; $i++) {
            $postsForFirstUser[] = new EloquentBinPostModel([
                'name'=>'First user - post ' . $i,
            ]);

            $postsForSecondUser[] = EloquentBinPostModel::create([
                'name'=>'Second user - post ' . $i,
                'user_id'=>$secondUser->id,
            ]);
        }

        $firstUser->posts()->saveMany($postsForFirstUser);

        static::assertEquals(10, $firstUser->posts()->count());
        static::assertEquals(10, $secondUser->posts()->count());


        $foundUser = EloquentBinUserModel::with('posts')->find($firstUser->id);
        static::assertNotNull($foundUser);

        static::assertEquals(10, count($foundUser->posts));

        $foundUser = EloquentBinUserModel::with('posts')->find($secondUser->id);
        static::assertNotNull($foundUser);
        static::assertEquals(10, count($foundUser->posts));
    }

    public function testRelationshipWithBinUuidOptimized()
    {
        $firstUser = EloquentBinOptimizedUserModel::create([
            'username'=>'first-user',
            'password'=>'secret'
        ]);

        $secondUser = EloquentBinOptimizedUserModel::create([
            'username'=>'second-user',
            'password'=>'secret'
        ]);

        $postsForFirstUser = [];
        $postsForSecondUser = [];

        for ($i=0; $i < 10; $i++) {
            $postsForFirstUser[] = new EloquentBinOptimizedPostModel([
                'name'=>'First user - post ' . $i,
            ]);

            $postsForSecondUser[] = EloquentBinOptimizedPostModel::create([
                'name'=>'Second user - post ' . $i,
                'user_id'=>$secondUser->id,
            ]);
        }

        $firstUser->posts()->saveMany($postsForFirstUser);

        static::assertEquals(10, $firstUser->posts()->count());
        static::assertEquals(10, $secondUser->posts()->count());


        $foundUser = EloquentBinOptimizedUserModel::with('posts')->find($firstUser->id);
        static::assertNotNull($foundUser);

        static::assertEquals(10, count($foundUser->posts));

        $foundUser = EloquentBinOptimizedUserModel::with('posts')->find($secondUser->id);
        static::assertNotNull($foundUser);
        static::assertEquals(10, count($foundUser->posts));
    }

    public function testManyToManyRelationshipsWithChar32()
    {
        $firstUser = Eloquent32UserModel::create([
            'username'=>'first-user',
            'password'=>'secret'
        ]);

        $secondUser = Eloquent32UserModel::create([
            'username'=>'second-user',
            'password'=>'secret'
        ]);

        $thirdUser = Eloquent32UserModel::create([
            'username' => 'third-user',
            'password' => 'secret'
        ]);

        $firstRole = Eloquent32RoleModel::create([
            'name' => 'Sailor',
        ]);
        $secondRole = Eloquent32RoleModel::create([
            'name' => 'Cook',
        ]);
        $thirdRole = Eloquent32RoleModel::create([
            'name' => 'Pirate',
        ]);

        $firstUser->roles()->attach([$firstRole->id, $secondRole->id]);

        $crusoe = Eloquent32UserModel::find($firstUser->id);
        static::assertEquals(2, $crusoe->roles()->count());

        $secondUser->roles()->attach([$firstRole->id, $secondRole->id]);
        $secondUser->roles()->sync([$secondRole->id, $thirdRole->id]);

        $crusoe = Eloquent32UserModel::find($secondUser->id);
        $found = false;
        foreach ($crusoe->roles as $role) {
            if ($role->id === $thirdRole->id) {
                $found = true;
            }
        }
        static::assertTrue($found);
    }

    public function testManyToManyRelationshipsWithBin()
    {
        $firstUser = EloquentBinUserModel::create([
            'username'=>'first-user',
            'password'=>'secret'
        ]);

        $secondUser = EloquentBinUserModel::create([
            'username'=>'second-user',
            'password'=>'secret'
        ]);

        $thirdUser = EloquentBinUserModel::create([
            'username' => 'third-user',
            'password' => 'secret'
        ]);

        $firstRole = EloquentBinRoleModel::create([
            'name' => 'Sailor',
        ]);
        $secondRole = EloquentBinRoleModel::create([
            'name' => 'Cook',
        ]);
        $thirdRole = EloquentBinRoleModel::create([
            'name' => 'Pirate',
        ]);

        $firstUser->roles()->attach([$firstRole->id, $secondRole->id]);

        $crusoe = EloquentBinUserModel::find($firstUser->id);
        static::assertEquals(2, $crusoe->roles()->count());


        $secondUser->roles()->attach([$firstRole->id, $secondRole->id]);
        $secondUser->roles()->sync([$secondRole->id, $thirdRole->id]);
        $crusoe = EloquentBinUserModel::find($secondUser->id);

        $found = false;
        foreach ($crusoe->roles as $role) {
            if ($role->id === $thirdRole->id) {
                $found = true;
            }
        }
        static::assertTrue($found);
    }

    public function testJsonBinary()
    {
        $serializable = EloquentBinUserModel::create([
            'username' => 'Serializable',
            'password' => 'secret'
        ]);

        $json = $serializable->toJson();
        static::assertNotNull($json);
    }

    public function testJsonOptimizedBinary()
    {
        $serializable = EloquentBinOptimizedUserModel::create([
            'username' => 'Serializable',
            'password' => 'secret'
        ]);

        $json = $serializable->toJson();
        static::assertNotNull($json);
    }

    public function testJsonCustomAtributesBinary()
    {
        $serializable = EloquentBinUserWAModel::create([
            'username' => 'Serializable',
            'password' => 'secret',
        ]);

        $json = $serializable->toJson();
        static::assertNotNull($json);
    }

    /**
     * Bootstrap Eloquent.
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {

        Eloquent::setConnectionResolver(
            new DatabaseIntegrationTestConnectionResolver
        );

        Eloquent::setEventDispatcher(
            new Illuminate\Events\Dispatcher
        );
    }

    /**
     * Tear down Eloquent.
     */
    public static function tearDownAfterClass()
    {
        Eloquent::unsetEventDispatcher();
        Eloquent::unsetConnectionResolver();
    }

    /**
     * Setup the database schema.
     *
     * @return void
     */
    public function setUp()
    {
        $this->schema()->create('users', function ($table) {
            $table->char('id', 36);
            $table->string('username');
            $table->string('password');
            $table->timestamps();
            $table->primary('id');
        });

        $this->schema()->create('posts', function ($table) {
            // Can be in Laravel 5.2
            // $this->uuid('id');
            $table->char('id', 36);
            $table->string('name');
            $table->char('user_id', 36);
            $table->timestamps();
            $table->primary('id');
        });

        $this->schema()->create('users32', function ($table) {
            // this is not a mistake, we need to be sure the field is not stripped down by the DB
            $table->char('id', 36);
            $table->string('username');
            $table->string('password');
            $table->timestamps();
            $table->primary('id');
        });

        $this->schema()->create('posts32', function ($table) {
            $table->char('id', 36);
            $table->string('name');
            $table->char('user_id', 36);
            $table->timestamps();
            $table->primary('id');
        });

        $this->schema()->create('usersb', function ($table) {
            $table->string('username');
            $table->string('password');
            $table->timestamps();
        });

        $this->schema()->create('postsb', function ($table) {
            $table->string('name');
            $table->timestamps();
        });

        $this->schema()->create('roles32', function ($table) {
            $table->char('id', 32);
            $table->string('name');
            $table->timestamps();
        });

        $this->schema()->create('user32_role32', function ($table) {
            $table->char('user_id', 32);
            $table->char('role_id', 32);
        });

        $this->schema()->create('rolesb', function ($table) {
            $table->string('name');
            $table->timestamps();
        });

        $this->schema()->create('userb_roleb', function ($table) {
            $table->nullableTimestamps();
        });

        // unfortunately, we need to do this:
        // DB::statement (...)
        $this->connection()->statement('ALTER TABLE `usersb` ADD `id` BINARY(16); ALTER TABLE `usersb` ADD PRIMARY KEY (`id`);');
        $this->connection()->statement('ALTER TABLE `postsb` ADD COLUMN `id` BINARY(16); ALTER TABLE `postsb` ADD PRIMARY KEY (`id`);');
        $this->connection()->statement('ALTER TABLE `postsb` ADD COLUMN `user_id` BINARY(16);');

        $this->connection()->statement('ALTER TABLE `rolesb` ADD `id` BINARY(16); ALTER TABLE `rolesb` ADD PRIMARY KEY (`id`);');
        $this->connection()->statement('ALTER TABLE `userb_roleb` ADD `user_id` BINARY(16) DEFAULT NULL;');
        $this->connection()->statement('ALTER TABLE `userb_roleb` ADD `role_id` BINARY(16) DEFAULT NULL;');
        // $this->connection()->statement('ALTER TABLE `userb_roleb` ADD PRIMARY KEY (user_id, role_id);');
    }

    /**
     * Tear down the database schema.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->schema()->drop('users');
        $this->schema()->drop('users32');
        $this->schema()->drop('usersb');
        $this->schema()->drop('posts');
        $this->schema()->drop('posts32');
        $this->schema()->drop('postsb');
        $this->schema()->drop('rolesb');
        $this->schema()->drop('roles32');
        $this->schema()->drop('user32_role32');
        $this->schema()->drop('userb_roleb');
    }

    /**
     * Helpers...
     */

    /**
     * Get a database connection instance.
     *
     * @return Connection
     */
    protected function connection()
    {
        return Eloquent::getConnectionResolver()->connection();
    }

    /**
     * Get a schema builder instance.
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    protected function schema()
    {
        return $this->connection()->getSchemaBuilder();
    }
}



class EloquentUserModel extends Eloquent
{
    use UuidModelTrait;
    protected $table = 'users';

    protected $guarded = [];

    public function posts()
    {
        return $this->hasMany('EloquentPostModel', 'user_id');
    }
}

class Eloquent32UserModel extends Eloquent
{
    use Uuid32ModelTrait;
    protected $table = 'users32';

    protected $guarded = [];

    public function posts()
    {
        return $this->hasMany('Eloquent32PostModel', 'user_id');
    }

    public function roles()
    {
        return $this->belongsToMany('Eloquent32RoleModel', 'user32_role32', 'user_id', 'role_id');
    }
}

class EloquentBinUserModel extends Eloquent
{
    use UuidBinaryModelTrait;
    protected $table = 'usersb';

    protected $guarded = [];

    public function posts()
    {
        return $this->hasMany('EloquentBinPostModel', 'user_id');
    }

    public function roles() {
        return $this->belongsToMany('EloquentBinRoleModel', 'userb_roleb', 'user_id', 'role_id');
    }
}

class EloquentBinOptimizedUserModel extends Eloquent
{
    use UuidBinaryModelTrait;
    protected $table = 'usersb';

    protected $guarded = [];
    protected static $uuidOptimization = true;

    public function posts()
    {
        return $this->hasMany('EloquentBinOptimizedPostModel', 'user_id');
    }
}

class EloquentPostModel extends Eloquent
{
    use UuidModelTrait;
    protected $table = 'posts';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('EloquentUserModel', 'user_id');
    }
}

class Eloquent32PostModel extends Eloquent
{
    use Uuid32ModelTrait;
    protected $table = 'posts32';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('Eloquent32UserModel', 'user_id');
    }
}

class EloquentBinPostModel extends Eloquent
{
    use UuidBinaryModelTrait;
    protected $table = 'postsb';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('EloquentBinUserModel', 'user_id');
    }
}

class EloquentBinOptimizedPostModel extends Eloquent
{
    use UuidBinaryModelTrait;
    protected $table = 'postsb';

    protected $guarded = [];
    protected static $uuidOptimization = true;

    public function user()
    {
        return $this->belongsTo('EloquentBinOptimizedUserModel', 'user_id');
    }
}

class Eloquent32RoleModel extends Eloquent
{
    use Uuid32ModelTrait;
    protected $table = 'roles32';

    protected $guarded = [];

    public function users()
    {
        return $this->belongsToMany(Eloquent32UserModel::class, 'user32_role32', 'role_id', 'user_id');
    }
}

class EloquentBinRoleModel extends Eloquent
{
    use UuidBinaryModelTrait;
    protected $table = 'rolesb';

    protected $guarded = [];
    public function users()
    {
        return $this->belongsToMany(EloquentBinUserModel::class, 'userb_roleb', 'role_id', 'user_id');
    }
}

class EloquentBinUserWAModel extends Eloquent
{
    use UuidBinaryModelTrait;
    protected $table = 'usersb';

    protected $guarded = [];

    protected $appends = ['custObj', 'custArr', 'custComp'];

    public function getCustObjAttribute()
    {
//        $c = new stdClass;
//        $c->nice = 'Hello World';
//        $c->type = 'i am an object'
//        return $c;

        return (object)[
            'nice' => 'Hello World',
            'type' => 'i am an object'
        ];
    }

    public function getCustArrAttribute()
    {
        return [
            'nice' => 'Hello World',
            'type' => 'i am an array'
        ];
    }

    public function getCustCompAttribute()
    {
        return 'My Name is: '  . $this->name;
    }
}

class DatabaseIntegrationTestConnectionResolver implements Illuminate\Database\ConnectionResolverInterface
{
    protected $connection;

    public function connection($name = null)
    {
        if (isset($this->connection)) {
            return $this->connection;
        }

        return $this->connection = new Illuminate\Database\SQLiteConnection(new PDO('sqlite::memory:'));
    }

    public function getDefaultConnection()
    {
        return 'default';
    }

    public function setDefaultConnection($name)
    {
        //
    }
}
