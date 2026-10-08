<?php
require __DIR__ . '/auth.php'; requireAdmin(); require __DIR__ . '/layout.php';
$db=getDB();$id=(int)($_GET['id']??0);$stmt=$db->prepare('SELECT * FROM blog_posts WHERE id=?');$stmt->execute([$id]);$post=$stmt->fetch();
if(!$post){http_response_code(404);exit('Blog not found.');}
$content=html_entity_decode(strip_tags(str_replace(['<br />','<br>','</p>'],["\n","\n","\n\n"],$post['content'])),ENT_QUOTES,'UTF-8');$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $post['title']=trim($_POST['title']??'');$post['excerpt']=trim($_POST['excerpt']??'');$content=trim($_POST['content']??'');$post['is_published']=isset($_POST['is_published'])?1:0;
 try{
 if($post['title']===''||mb_strlen($post['title'])>255||$content===''){throw new RuntimeException('Enter a title (up to 255 characters) and content.');}
 $image=handleBlogUpload('image')??$post['image'];
 $stmt=$db->prepare('UPDATE blog_posts SET title=?,excerpt=?,content=?,image=?,is_published=? WHERE id=?');$stmt->execute([$post['title'],$post['excerpt'],nl2br(e($content)),$image,$post['is_published'],$id]);header('Location: blogs.php');exit;
 }catch(RuntimeException $ex){$error=$ex->getMessage();}
}
adminHeader('Edit blog'); ?>
<?php if($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
<div class="admin-panel"><form method="post" enctype="multipart/form-data" class="admin-form"><?= adminTokenField() ?>
<label>Title<input name="title" maxlength="255" required value="<?= e($post['title']) ?>"></label>
<label>Short description<textarea name="excerpt" rows="3"><?= e($post['excerpt']) ?></textarea></label>
<label>Blog content<textarea name="content" rows="10" required><?= e($content) ?></textarea></label>
<img src="../<?= e(productImageUrl($post['image'],'assets/images/placeholder-blog.svg')) ?>" alt="Current blog image" style="max-width:180px">
<label>Blog image<input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"><small>Leave empty to keep the current image.</small></label>
<label><input type="checkbox" name="is_published" value="1" style="width:auto" <?= $post['is_published']?'checked':'' ?>> Publish on website</label>
<button class="btn btn-primary">Save changes</button> <a class="btn btn-outline" href="blogs.php">Cancel</a></form></div><?php adminFooter(); ?>
