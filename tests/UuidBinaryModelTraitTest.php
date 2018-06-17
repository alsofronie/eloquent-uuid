<?php

namespace Tests;

use Alsofronie\Uuid\UuidBinaryModelTrait;
use Illuminate\Database\Eloquent\Model as Eloquent;
use PHPUnit\Framework\TestCase;

/**
 * Test the UuidBinaryModelTrait
 */
class UuidBinaryModelTraitTest extends TestCase implements EloquentUuidTestable
{
	public function testCreation(){
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

	public function testFind(){
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

	public function testFindOrFail(){
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

	    public function testFindFromStringUuid()
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

    public function testFindOrFailFromStringUuid()
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

	public function testRelationship(){
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

	public function testManyToMany(){
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

	public function testToJson()
    {
        $serializable = EloquentBinUserModel::create([
            'username' => 'Serializable',
            'password' => 'secret'
        ]);

        $json = $serializable->toJson();
        static::assertNotNull($json);
    }

    public function testToJsonWithUTF8Symbols()
    {
        $serializable = EloquentBinUserModel::create([
            'username' => 'Gérard François with the 2£ sign',
            'password' => 'The biggest secret of all time'
        ]);

        $json = $serializable->toJson();
        static::assertNotNull($json);
    }

    public function testJsonCustomAtributes()
    {
        $serializable = EloquentBinUserWAModel::create([
            'username' => 'Serializable',
            'password' => 'secret',
        ]);

        $json = $serializable->toJson();
        static::assertNotNull($json);
    }

    public function testToArrayWithUTF8Symbols()
    {
        $serializable = EloquentBinUserModel::create([
            'username' => 'Gérard François with the 2£ sign. \n And line break!',
            'password' => 'The biggest secret of all time'
        ]);
        $array = $serializable->toArray();
        static::assertEquals('Gérard François with the 2£ sign. \n And line break!', $array['username']);
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

class EloquentBinUserModel extends Eloquent
{
    use UuidBinaryModelTrait;
    protected $table = 'usersb';

    protected $guarded = [];

    public function posts()
    {
        return $this->hasMany('Tests\EloquentBinPostModel', 'user_id');
    }

    public function roles() {
        return $this->belongsToMany('Tests\EloquentBinRoleModel', 'userb_roleb', 'user_id', 'role_id');
    }
}

class EloquentBinPostModel extends Eloquent
{
    use UuidBinaryModelTrait;
    protected $table = 'postsb';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('Test\EloquentBinUserModel', 'user_id');
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