<?php

namespace Tests;

use Alsofronie\Uuid\UuidBinaryModelTrait;
use Illuminate\Database\Eloquent\Model as Eloquent;
use PHPUnit\Framework\TestCase;

/**
 * Test the UuidBinaryModelTrait with the optimized flag enabled
 */
class UuidBinaryModelOptimizedTraitTest extends TestCase implements EloquentUuidTestable
{
	public function testCreation(){
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

	public function testFind(){
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

	public function testFindOrFail(){
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

	public function testRelationship(){
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

	public function testToJson()
    {
        $serializable = EloquentBinOptimizedUserModel::create([
            'username' => 'Serializable',
            'password' => 'secret'
        ]);
        $json = $serializable->toJson();
        static::assertNotNull($json);
    }

    public function testToArray()
    {
        $model = EloquentBinOptimizedUserModel::create([
            'username' => 'Model',
            'password' => 'secret'
        ]);
        $customModel = EloquentBinOptimizedPostModel::create(['name' => 'bla']);
        $model->cust = $customModel;

        $array = $model->toArray();
        static::assertNotNull($array);

        $json = json_encode($array);
        static::assertNotNull($json);
    }

    public function testToArrayWithUTF8AndNewline()
    {
        $model = EloquentBinOptimizedUserModel::create([
            'username' => 'Grève! \n Or not?',
            'password' => 'secret'
        ]);
        $customModel = EloquentBinOptimizedPostModel::create(['name' => 'bla']);
        $model->cust = $customModel;

        $array = $model->toArray();
        static::assertEquals('Grève! \n Or not?', $model->username);
        static::assertNotNull($array);

        $json = json_encode($array);
        static::assertNotNull($json);
    }

	public function testManyToMany(){
		$firstUser = EloquentBinOptimizedUserModel::create([
            'username'=>'first-user',
            'password'=>'secret'
        ]);

        $secondUser = EloquentBinOptimizedUserModel::create([
            'username'=>'second-user',
            'password'=>'secret'
        ]);

        $thirdUser = EloquentBinOptimizedUserModel::create([
            'username' => 'third-user',
            'password' => 'secret'
        ]);

        $firstRole = EloquentBinOptimizedRoleModel::create([
            'name' => 'Sailor',
        ]);
        $secondRole = EloquentBinOptimizedRoleModel::create([
            'name' => 'Cook',
        ]);
        $thirdRole = EloquentBinOptimizedRoleModel::create([
            'name' => 'Pirate',
        ]);

        $firstUser->roles()->attach([$firstRole->id, $secondRole->id]);

        $crusoe = EloquentBinOptimizedUserModel::find($firstUser->id);
        static::assertEquals(2, $crusoe->roles()->count());


        $secondUser->roles()->attach([$firstRole->id, $secondRole->id]);
        $secondUser->roles()->sync([$secondRole->id, $thirdRole->id]);
        $crusoe = EloquentBinOptimizedUserModel::find($secondUser->id);

        $found = false;
        foreach ($crusoe->roles as $role) {
            if ($role->id === $thirdRole->id) {
                $found = true;
            }
        }
        static::assertTrue($found);
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
            new \Illuminate\Events\Dispatcher
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
       $this->schema()->create('usersb', function ($table) {
            $table->string('username');
            $table->string('password');
            $table->timestamps();
        });

        $this->schema()->create('postsb', function ($table) {
            $table->string('name');
            $table->timestamps();
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
        $this->schema()->drop('usersb');
        $this->schema()->drop('postsb');
        $this->schema()->drop('rolesb');
        $this->schema()->drop('userb_roleb');
    }

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

class EloquentBinOptimizedUserModel extends Eloquent
{
    use UuidBinaryModelTrait;
    protected $table = 'usersb';

    protected $guarded = [];
    protected static $uuidOptimization = true;

    public function posts()
    {
        return $this->hasMany('Tests\EloquentBinOptimizedPostModel', 'user_id');
    }

    public function roles()
    {
    	return $this->belongsToMany('Tests\EloquentBinOptimizedRoleModel', 'userb_roleb', 'user_id', 'role_id');
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
        return $this->belongsTo('Tests\EloquentBinOptimizedUserModel', 'user_id');
    }
}

class EloquentBinOptimizedRoleModel extends Eloquent
{
    use UuidBinaryModelTrait;
    protected $table = 'rolesb';

    protected $guarded = [];
    protected static $uuidOptimization = true;

    public function users()
    {
        return $this->belongsToMany('Tests\EloquentBinOptimizedUserModel', 'userb_roleb', 'role_id', 'user_id');
    }
}