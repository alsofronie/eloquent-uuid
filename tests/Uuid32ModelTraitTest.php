<?php

namespace Tests;

use Alsofronie\Uuid\Uuid32ModelTrait;
use Illuminate\Database\Eloquent\Model as Eloquent;
use PHPUnit\Framework\TestCase;

/**
 * Test the Uuid32ModelTrait
 */
class Uuid32ModelTraitTest extends TestCase implements EloquentUuidTestable
{
	public function testCreation(){
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

	public function testFind(){
		$creation = Eloquent32UserModel::create([
            'username'=>'alsofronie',
            'password'=>'secret'
        ]);

		$found = Eloquent32UserModel::find($creation->id);
		static::assertNotNull($found);
		static::assertEquals($creation->id, $found->id);
	}

	public function testFindOrFail(){
		$creation = Eloquent32UserModel::create([
            'username'=>'alsofronie',
            'password'=>'secret'
        ]);

		$found = Eloquent32UserModel::findOrFail($creation->id);
		static::assertNotNull($found);
		static::assertEquals($creation->id, $found->id);
	}

	public function testRelationship(){
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

	public function testManyToMany(){
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

        $this->schema()->create('roles32', function ($table) {
            $table->char('id', 32);
            $table->string('name');
            $table->timestamps();
        });

        $this->schema()->create('user32_role32', function ($table) {
            $table->char('user_id', 32);
            $table->char('role_id', 32);
        });
    }

    /**
     * Tear down the database schema.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->schema()->drop('users32');
        $this->schema()->drop('posts32');
        $this->schema()->drop('roles32');
        $this->schema()->drop('user32_role32');
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

class Eloquent32UserModel extends Eloquent
{
    use Uuid32ModelTrait;
    protected $table = 'users32';

    protected $guarded = [];

    public function posts()
    {
        return $this->hasMany('Tests\Eloquent32PostModel', 'user_id');
    }

    public function roles()
    {
        return $this->belongsToMany('Tests\Eloquent32RoleModel', 'user32_role32', 'user_id', 'role_id');
    }
}

class Eloquent32PostModel extends Eloquent
{
    use Uuid32ModelTrait;
    protected $table = 'posts32';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('Tests\Eloquent32UserModel', 'user_id');
    }
}

class Eloquent32RoleModel extends Eloquent
{
    use Uuid32ModelTrait;
    protected $table = 'roles32';

    protected $guarded = [];

    public function users()
    {
        return $this->belongsToMany('Tests\Eloquent32UserModel', 'user32_role32', 'role_id', 'user_id');
    }
}