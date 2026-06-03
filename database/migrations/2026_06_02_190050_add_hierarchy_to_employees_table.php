<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHierarchyToEmployeesTable extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            // Defines who the user is (HR, TL, or standard Employee)
            $table->string('role')->default('Employee')->after('email');
            
            // Creates the relational link to the Team Lead
            $table->unsignedBigInteger('tl_id')->nullable()->after('role');
            $table->foreign('tl_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['tl_id']);
            $table->dropColumn(['role', 'tl_id']);
        });
    }
}