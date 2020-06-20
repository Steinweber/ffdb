<?php
namespace FFDB\Instance;

interface Method
{
    public function insert($value);

    public function update($value);

    public function updateData(Data $data);

    public function updateArray(array $data);

    public function delete($key);

    public function get($key);

    public function has($key);

    public function filter();

    public function data();

    public function save();
}