<?php
namespace MiniOrange\Helper;

use Illuminate\Database\Capsule\Manager as LaraDB;
use Illuminate\Routing\Controller;
use Artisan;
use Illuminate\Support\Facades\Schema;

if(!Schema::hasTable('mo_admin') || !Schema::hasTable('mo_config')) {
    echo "here";exit;
    Artisan::call('migrate', array('--path' => __DIR__.'../2014_10_12_100000_create_miniorange_tables.php','--force'=>TRUE));}

class DB extends Controller
{

    public static function get_option($key)
    {
        self::startConnection();
        $option = LaraDB::table('mo_config')->first()->$key;
        return $option;
    }

    public static function update_option($key, $value)
    {
        self::startConnection();
        LaraDB::table('mo_config')->where('id', 1)->update([
            $key => $value
        ]);
    }

    public static function delete_option($key)
    {
        self::startConnection();
        LaraDB::table('mo_config')->where('id', 1)->update([
            $key => ''
        ]);
    }

    protected static function get_options()
    {
        self::startConnection();
        $active_config = LaraDB::table('mo_config')->get()->first();
        return $active_config;
    }
    
    public static function get_registered_user()
    {
        self::startConnection();
        $registered_user = LaraDB::table('mo_admin')->get()->first();
        return $registered_user;
    }

    public static function register_user($email, $password){
        self::startConnection();
        LaraDB::table('mo_admin')->updateOrInsert(['id' => 1],['email' => $email,'password' => $password]);
    }
    protected static function startConnection()
    {
        $connection = array(
            'driver' => getenv('DB_CONNECTION'),
            'host' => getenv('DB_HOST'),
            'port' => getenv('DB_PORT'),
            'database' => getenv('DB_DATABASE'),
            'username' => getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD')
        );
        $Capsule = new LaraDB();
        $Capsule->addConnection($connection);
        $Capsule->setAsGlobal(); // this is important
        $Capsule->bootEloquent();
        if(LaraDB::table('mo_config')->get()->first()==NULL)
        {
            if(LaraDB::table('mo_config')->updateOrInsert(
                ['id' => 1],['mo_saml_host_name' => 'https://auth.miniorange.com']))
            {
                
            }
        }
    }
}
?>
