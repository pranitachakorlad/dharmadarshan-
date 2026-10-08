<?php
require __DIR__ . '/auth.php'; requireAdmin(); require __DIR__ . '/layout.php';
$db=getDB(); $id=(int)($_GET['id']??0);$item=['name'=>'','price'=>'','description'=>'','category_slug'=>$_GET['cat']??''];$error='';
if($id){$stmt=$db->prepare('SELECT p.*,c.slug AS category_slug FROM products p JOIN categories c ON c.id=p.category_id WHERE p.id=?');$stmt->execute([$id]);$item=$stmt->fetch();if(!$item){http_response_code(404);exit('Product not found.');}}
if($_SERVER['REQUEST_METHOD']==='POST'){
 $item=array_merge($item,['name'=>trim($_POST['name']??''),'price'=>trim($_POST['price']??''),'description'=>trim($_POST['description']??''),'category_slug'=>$_POST['category']??'']);
 try{
 if(!shopCollectionCardBySlug($item['category_slug'])){throw new RuntimeException('Select a category.');}
 if($item['name']==='' || mb_strlen($item['name'])>200 || $item['description']===''){throw new RuntimeException('Enter a product name (up to 200 characters) and description.');}
 if(!preg_match('/^\d{1,8}(\.\d{1,2})?$/',$item['price'])){throw new RuntimeException('Enter a valid price with up to two decimal places.');}
 $image=handleProductUpload('image')??($item['image']??null);if(!$image){throw new RuntimeException('Choose a product image.');}
 $cat=getCategoryBySlug($db,$item['category_slug']);
 if($id){$stmt=$db->prepare('UPDATE products SET category_id=?,name=?,price=?,description=?,image=? WHERE id=?');$stmt->execute([$cat['id'],$item['name'],$item['price'],$item['description'],$image,$id]);}
 else{$stmt=$db->prepare('INSERT INTO products(category_id,name,price,description,image,is_active) VALUES(?,?,?,?,?,1)');$stmt->execute([$cat['id'],$item['name'],$item['price'],$item['description'],$image]);}
 header('Location: products.php?cat='.urlencode($item['category_slug']));exit;
 }catch(RuntimeException $ex){$error=$ex->getMessage();}
}
adminHeader($id?'Edit product':'Add product'); ?>
<p>Products appear automatically in the category you select.</p><?php if($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
<div class="admin-panel"><form class="admin-form" method="post" enctype="multipart/form-data"><?= adminTokenField() ?>
<label>Category<select name="category" required><option value="">Select category</option><?php foreach(shopCollectionCards() as $card): ?><option value="<?= e($card['slug']) ?>" <?= $item['category_slug']===$card['slug']?'selected':'' ?>><?= e($card['name']) ?></option><?php endforeach; ?></select></label>
<label>Product image<input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif" <?= $id?'':'required' ?>><small>JPG, PNG, WebP or GIF. Up to 5 MB.<?= $id?' Leave empty to keep the current image.':'' ?></small></label><?php if(!empty($item['image'])): ?><img src="../<?= e($item['image']) ?>" alt="Current image" style="max-width:160px"><?php endif; ?>
<label>Product name<input name="name" maxlength="200" required value="<?= e($item['name']) ?>"></label>
<label>Price (Rs.)<input type="number" name="price" min="0" max="99999999.99" step="0.01" required value="<?= e((string)$item['price']) ?>"></label>
<label>Description<textarea name="description" rows="5" required><?= e($item['description']) ?></textarea></label><button class="btn btn-primary">Save product</button> <a href="products.php" class="btn btn-outline">Cancel</a></form></div><?php adminFooter(); ?>
