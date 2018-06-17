<?php

namespace Tests;

interface EloquentUuidTestable {
	public function testCreation();
	public function testFind();	
	public function testFindOrFail();
	public function testRelationship();
	public function testManyToMany();
	
}