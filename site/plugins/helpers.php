<?php

function addToStructure($page, $field, $data = array())
{
  $fieldData = $page->$field()->yaml();
  $key = array_search($data['sku'], array_column($fieldData, 'sku'));
  unset($fieldData[$key]);

  $fieldData[] = $data;
  $fieldData = yaml::encode($fieldData);
  try {
    $page->update(array($field => $fieldData));
    return true;
  } catch(Exception $e) {
    return $e->getMessage();
  }
}

function inStock($variant)
{
  if(strstr($variant, '::')){
    $idParts = explode('::',$variant);
    $uri = $idParts[0];
    $sku = $idParts[1];

    $variant = page($uri)->variants()->toStructure()->findBy('sku', $sku);
    return $variant->stock->value();
  }

  if (!is_numeric($variant->stock()->value) and $variant->stock()->value === '') return true;
  if (is_numeric($variant->stock()->value) and intval($variant->stock()->value) <= 0) return false;
  if (is_numeric($variant->stock()->value) and intval($variant->stock()->value) > 0) return intval($variant->stock()->value);

  return false;
}

function getUniqueId($type = 'sku')
{
  $prefix = 'pre_';

  switch($type){
    case 'sku':
      $prefix = 'sku_';
      break;
    case 'product':
      $prefix = 'prd_';
      break;
    case 'order':
      $prefix = 'ord_';
      break;
  }

  $bytes = openssl_random_pseudo_bytes(14, $cstrong);
  $hex   = bin2hex($bytes);

  return $prefix . $hex;
}
