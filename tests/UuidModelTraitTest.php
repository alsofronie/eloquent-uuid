<?php

namespace Tests;

use Alsofronie\Uuid\UuidModelTrait;
use Illuminate\Database\Eloquent\Model as Eloquent;
use PHPUnit\Framework\TestCase;

/**
 * Test the UuidModelTrait
 */
class UuidModelTraitTest extends TestCase implements EloquentUuidTestable
{
	/**
     * Tests the creation of model with uuid as primary key
     *
     * @return void
     */
	public function testCreation(){
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

	public function testFind(){
		$model = EloquentUserModel::create([
            'username'=>'alsofronie',
            'password'=>'secret'
        ]);

        $foundUser = EloquentUserModel::find($model->id);
        static::assertNotNull($foundUser);
        static::assertEquals('alsofronie', $foundUser->username);
	}

	public function testFindOrFail(){
		$model = EloquentUserModel::create([
            'username'=>'alsofronie',
            'password'=>'secret'
        ]);

        $foundUser = EloquentUserModel::findOrFail($model->id);
        static::assertNotNull($foundUser);
        static::assertEquals('alsofronie', $foundUser->username);
	}

	public function testRelationship(){
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

	public function testManyToMany(){
		$firstUser = EloquentUserModel::create([
            'username'=>'alsofronie',
            'password'=>'secret'
        ]);

        $secondUser = EloquentUserModel::create([
            'username'=>'jvalck',
            'password'=>'secret'
        ]);

        $role = EloquentRoleModel::create([
        	'name' => 'gh',
        ]);

        static::assertEquals(0, $firstUser->roles()->count());
        $role->users()->attach([$firstUser->id, $secondUser->id]);
        $count = EloquentRoleModel::find($role->id)->users()->count();
        static::assertEquals(2, $count);
        static::assertEquals(1, $secondUser->roles()->count());
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
        $this->schema()->create('users', function ($table) {
            $table->char('id', 36);
            $table->string('username');
            $table->string('password');
            $table->timestamps();
            $table->primary('id');
        });

        $this->schema()->create('roles', function ($table) {
            $table->char('id', 36);
            $table->string('name');
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

        $this->schema()->create('user_role', function ($table) {
            $table->char('user_id', 36);
            $table->char('role_id', 36);
        });     
    }

    /**
     * Tear down the database schema.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->schema()->drop('users');        
        $this->schema()->drop('posts');        
        $this->schema()->drop('roles'); 
        $this->schema()->drop('user_role'); 
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

class EloquentUserModel extends Eloquent
{
    use UuidModelTrait;
    protected $table = 'users';

    protected $guarded = [];

    public function posts()
    {
        return $this->hasMany('Tests\EloquentPostModel', 'user_id');
    }

    public function roles()
    {
        return $this->belongsToMany('Tests\EloquentRoleModel', 'user_role', 'user_id', 'role_id');
    }
}

class EloquentPostModel extends Eloquent
{
    use UuidModelTrait;
    protected $table = 'posts';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('Tests\EloquentUserModel', 'user_id');
    }
}

class EloquentRoleModel extends Eloquent
{
    use UuidModelTrait;
    protected $table = 'roles';

    protected $guarded = [];

    public function users()
    {
        return $this->belongsToMany('Tests\EloquentUserModel', 'user_role', 'role_id', 'user_id');
    }
}