<?php

use Alsofronie\Uuid\UuidModelTrait;
use Alsofronie\Uuid\Uuid32ModelTrait;
use Alsofronie\Uuid\UuidBinaryModelTrait;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\DB;

class EloquentUuidTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests the creation of model with uuid as primary key
     *
     * @return void
     */
    public function testCreation()
    {
        // EloquentUserModel::unguard();
        $creation = EloquentUserModel::create([
            'username'=>'alsofronie',
            'password'=>'secret'
        ]);

        $this->assertEquals(36, strlen($creation->id));

        $model = EloquentUserModel::first();

        $this->assertEquals(36, strlen($model->id));
        $this->assertRegExp('/^[0-9a-f-]{36}$/', $model->id);
        $this->assertRegExp('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $model->id);

        $this->assertEquals($creation->id, $model->id);

        // EloquentuserModel::guard();

    }

    public function test32Creation()
    {
        $creation = Eloquent32UserModel::create([
            'username'=>'alsofronie',
            'password'=>'secret'
        ]);

        $this->assertEquals(32, strlen($creation->id));

        $model = Eloquent32UserModel::first();

        $this->assertEquals(32, strlen($model->id));
        $this->assertRegExp('/^[0-9a-f]{32}$/', $model->id);
        $this->assertRegExp('/^[0-9a-f]{32}$/', $model->id);

        $this->assertEquals($creation->id, $model->id);
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
        $this->assertEquals(16, strlen($binUuid));

        $this->assertEquals($creation->id, $model->id);

        $hexUuid = bin2hex($binUuid);
        // This is to be expected, but just to show...
        $this->assertEquals(32, strlen($hexUuid));

        $this->assertEquals($hexUuid, $model->id_string);

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

        $this->assertEquals($hexUuid, $model->id_string);

        $found = EloquentBinUserModel::find($binUuid);
        $this->assertEquals($found, $model);

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

        $this->assertEquals($hexUuid, $model->id_string);

        $found = EloquentBinUserModel::find($hexUuid);
        $this->assertEquals($found, $model);
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
        
        $this->assertEquals(10, $firstUser->posts()->count());
        $this->assertEquals(10, $secondUser->posts()->count());
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
        
        $this->assertEquals(10, $firstUser->posts()->count());
        $this->assertEquals(10, $secondUser->posts()->count());
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
        
        $this->assertEquals(10, $firstUser->posts()->count());
        $this->assertEquals(10, $secondUser->posts()->count());


        $foundUser = EloquentBinUserModel::with('posts')->find($firstUser->id);
        $this->assertNotNull($foundUser);

        $this->assertEquals(10, count($foundUser->posts));
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
            $table->char('id', 36); // this is not a mistake, we need to be sure the field is not stripped down by the DB
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

        // unfortunately, we need to do this:
        // DB::statement (...)
        $this->connection()->statement('ALTER TABLE `usersb` ADD `id` BINARY(16); ALTER TABLE `usersb` ADD PRIMARY KEY (`id`);');
        $this->connection()->statement('ALTER TABLE `postsb` ADD COLUMN `id` BINARY(16); ALTER TABLE `postsb` ADD PRIMARY KEY (`id`);');
        $this->connection()->statement('ALTER TABLE `postsb` ADD COLUMN `user_id` BINARY(16);');
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
     * @return Schema\Builder
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
}

class EloquentPostModel extends Eloquent
{
    use UuidModelTrait;
    protected $table = 'posts';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('EloquentUserModel,', 'user_id');
    }
}

class Eloquent32PostModel extends Eloquent
{
    use Uuid32ModelTrait;
    protected $table = 'posts32';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('Eloquent32UserModel,', 'user_id');
    }
}

class EloquentBinPostModel extends Eloquent
{
    use UuidBinaryModelTrait;
    protected $table = 'postsb';
    
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('EloquentBinUserModel,', 'user_id');
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
