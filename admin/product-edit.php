<?php
/**
 * ONS SOUND — WordPress-Style Add / Edit Product
 */

require_once __DIR__ . '/auth.php';
require_admin();

$pageTitle = 'Edit Product';
require_once __DIR__ . '/../models/Category.php';

$db = get_db();
$catModel = new Category();
$categories = $catModel->getActive();

if (!function_exists('compress_and_save_product_image')) {
    /**
     * Compress, optimize and save product images.
     * Uses GD if enabled to resize oversized images (e.g. max 1600px) and recompress with high quality.
     * Preserves PNG transparency without dark or distorted backgrounds.
     * Falls back seamlessly to move_uploaded_file() if GD is not available (file is already client-compressed).
     */
    function compress_and_save_product_image($tmpFilePath, $originalName, $uploadDir, $maxDim = 1600, $quality = 82) {
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            return null;
        }

        $fileName = 'prod_' . time() . '_' . rand(100, 999) . '.' . $ext;
        $targetPath = $uploadDir . $fileName;

        if (extension_loaded('gd') && function_exists('imagecreatefromstring')) {
            $imgData = @file_get_contents($tmpFilePath);
            if ($imgData !== false) {
                $src = @imagecreatefromstring($imgData);
                if ($src !== false) {
                    $origW = imagesx($src);
                    $origH = imagesy($src);

                    $newW = $origW;
                    $newH = $origH;
                    if ($origW > $maxDim || $origH > $maxDim) {
                        if ($origW > $origH) {
                            $newW = $maxDim;
                            $newH = (int)round(($origH * $maxDim) / $origW);
                        } else {
                            $newH = $maxDim;
                            $newW = (int)round(($origW * $maxDim) / $origH);
                        }
                    }

                    $dest = imagecreatetruecolor($newW, $newH);

                    if (in_array($ext, ['png', 'webp', 'gif'])) {
                        imagealphablending($dest, false);
                        imagesavealpha($dest, true);
                        $transparent = imagecolorallocatealpha($dest, 255, 255, 255, 127);
                        imagefilledrectangle($dest, 0, 0, $newW, $newH, $transparent);
                    }

                    imagecopyresampled($dest, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

                    $saved = false;
                    if ($ext === 'png') {
                        $saved = @imagepng($dest, $targetPath, 8);
                    } elseif (in_array($ext, ['jpg', 'jpeg'])) {
                        $saved = @imagejpeg($dest, $targetPath, $quality);
                    } elseif ($ext === 'webp' && function_exists('imagewebp')) {
                        $saved = @imagewebp($dest, $targetPath, $quality);
                    } elseif ($ext === 'gif' && function_exists('imagegif')) {
                        $saved = @imagegif($dest, $targetPath);
                    }

                    imagedestroy($dest);
                    imagedestroy($src);

                    if ($saved) {
                        return 'assets/images/products/' . $fileName;
                    }
                }
            }
        }

        // Direct save fallback: the file was already compressed before upload by client JS
        if (@move_uploaded_file($tmpFilePath, $targetPath) || @copy($tmpFilePath, $targetPath)) {
            return 'assets/images/products/' . $fileName;
        }

        return null;
    }
}

$productId = (int)($_GET['id'] ?? 0);
$isEdit = ($productId > 0);
$product = null;
$specifications = [];
$galleryImages = [];

if ($isEdit) {
    $stmt = $db->prepare("SELECT * FROM `products` WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        admin_set_flash('error', 'Product not found.');
        header("Location: " . admin_url('products.php'));
        exit;
    }

    // Load Specifications
    $stmt = $db->prepare("SELECT * FROM `product_specifications` WHERE product_id = ? ORDER BY display_order ASC, id ASC");
    $stmt->execute([$productId]);
    $specifications = $stmt->fetchAll();

    // Load Gallery Images
    $stmt = $db->prepare("SELECT * FROM `product_images` WHERE product_id = ? ORDER BY display_order ASC, id ASC");
    $stmt->execute([$productId]);
    $galleryImages = $stmt->fetchAll();
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        admin_set_flash('error', 'Security check failed. Please try again.');
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    $name = clean($_POST['name'] ?? '');
    $slug = clean($_POST['slug'] ?? '');
    $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $sku = clean($_POST['sku'] ?? '');
    $modelName = clean($_POST['model_name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $comparePrice = !empty($_POST['compare_at_price']) ? (float)$_POST['compare_at_price'] : null;
    $stockQuantity = (int)($_POST['stock_quantity'] ?? 0);
    $stockStatus = ($stockQuantity > 0) ? 'instock' : 'outofstock';
    if (!empty($_POST['stock_status_override'])) {
        $stockStatus = clean($_POST['stock_status_override']);
    }
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $mainImage = clean($_POST['main_image'] ?? 'assets/images/products/speaker-1.png');
    $hoverImage = clean($_POST['hover_image'] ?? '');
    $shortDesc = clean($_POST['short_description'] ?? '');
    $fullDesc = $_POST['full_description'] ?? '';



    $uploadDir = __DIR__ . '/../assets/images/products/';

    // Handle File Upload for Main Image if provided
    if (!empty($_FILES['main_image_file']['name']) && $_FILES['main_image_file']['error'] === UPLOAD_ERR_OK) {
        $savedMain = compress_and_save_product_image($_FILES['main_image_file']['tmp_name'], $_FILES['main_image_file']['name'], $uploadDir);
        if ($savedMain) {
            $mainImage = $savedMain;
        }
    }

    // Handle File Upload for Hover Image if provided
    if (!empty($_FILES['hover_image_file']['name']) && $_FILES['hover_image_file']['error'] === UPLOAD_ERR_OK) {
        $savedHover = compress_and_save_product_image($_FILES['hover_image_file']['tmp_name'], $_FILES['hover_image_file']['name'], $uploadDir);
        if ($savedHover) {
            $hoverImage = $savedHover;
        }
    }

    // Auto-generate slug if empty
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $slug = trim($slug, '-');
    }

    if (empty($name)) {
        admin_set_flash('error', 'Product name is required.');
    } else {
        try {
            if ($isEdit) {
                // Update Product
                $stmt = $db->prepare("
                    UPDATE `products` SET 
                        category_id = ?, name = ?, slug = ?, sku = ?, model_name = ?,
                        price = ?, compare_at_price = ?, stock_quantity = ?, stock_status = ?,
                        is_featured = ?, is_active = ?, main_image = ?, hover_image = ?,
                        short_description = ?, full_description = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([
                    $categoryId, $name, $slug, $sku, $modelName,
                    $price, $comparePrice, $stockQuantity, $stockStatus,
                    $isFeatured, $isActive, $mainImage, $hoverImage,
                    $shortDesc, $fullDesc, $productId
                ]);

                log_admin_activity('Update Product', "Updated product #$productId ($name)");
                $targetId = $productId;
                $flashMsg = "Product updated successfully.";
            } else {
                // Insert Product
                $stmt = $db->prepare("
                    INSERT INTO `products` (
                        category_id, name, slug, sku, model_name,
                        price, compare_at_price, stock_quantity, stock_status,
                        is_featured, is_active, main_image, hover_image,
                        short_description, full_description, created_at, updated_at
                    ) VALUES (
                        ?, ?, ?, ?, ?,
                        ?, ?, ?, ?,
                        ?, ?, ?, ?,
                        ?, ?, NOW(), NOW()
                    )
                ");
                $stmt->execute([
                    $categoryId, $name, $slug, $sku, $modelName,
                    $price, $comparePrice, $stockQuantity, $stockStatus,
                    $isFeatured, $isActive, $mainImage, $hoverImage,
                    $shortDesc, $fullDesc
                ]);

                $targetId = (int)$db->lastInsertId();
                log_admin_activity('Create Product', "Created new product #$targetId ($name)");
                $flashMsg = "Product published successfully.";
            }

            // Sync Specifications
            $db->prepare("DELETE FROM `product_specifications` WHERE product_id = ?")->execute([$targetId]);
            if (!empty($_POST['spec_keys']) && is_array($_POST['spec_keys'])) {
                $specStmt = $db->prepare("INSERT INTO `product_specifications` (product_id, spec_key, spec_value, display_order, created_at) VALUES (?, ?, ?, ?, NOW())");
                $order = 1;
                foreach ($_POST['spec_keys'] as $idx => $specKey) {
                    $sKey = clean($specKey);
                    $sVal = clean($_POST['spec_values'][$idx] ?? '');
                    if (!empty($sKey) && !empty($sVal)) {
                        $specStmt->execute([$targetId, $sKey, $sVal, $order++]);
                    }
                }
            }

            // Sync Gallery Images (Existing paths + newly uploaded compressed files)
            $db->prepare("DELETE FROM `product_images` WHERE product_id = ?")->execute([$targetId]);
            $galleryList = [];
            if (!empty($_POST['gallery_images']) && is_array($_POST['gallery_images'])) {
                foreach ($_POST['gallery_images'] as $imgPath) {
                    $cPath = clean($imgPath);
                    if (!empty($cPath)) {
                        $galleryList[] = $cPath;
                    }
                }
            }
            if (!empty($_FILES['gallery_image_files']['name']) && is_array($_FILES['gallery_image_files']['name'])) {
                $uploadDir = __DIR__ . '/../assets/images/products/';
                foreach ($_FILES['gallery_image_files']['name'] as $gIdx => $gName) {
                    if (!empty($gName) && isset($_FILES['gallery_image_files']['error'][$gIdx]) && $_FILES['gallery_image_files']['error'][$gIdx] === UPLOAD_ERR_OK) {
                        $gTmp = $_FILES['gallery_image_files']['tmp_name'][$gIdx];
                        $savedGal = compress_and_save_product_image($gTmp, $gName, $uploadDir);
                        if ($savedGal) {
                            $galleryList[] = $savedGal;
                        }
                    }
                }
            }
            if (!empty($galleryList)) {
                $galStmt = $db->prepare("INSERT INTO `product_images` (product_id, image_path, display_order, is_primary, created_at) VALUES (?, ?, ?, ?, NOW())");
                $gOrder = 1;
                foreach ($galleryList as $cPath) {
                    $isPrimary = ($gOrder === 1) ? 1 : 0;
                    $galStmt->execute([$targetId, $cPath, $gOrder++, $isPrimary]);
                }
            }

            admin_set_flash('success', $flashMsg);
            header("Location: " . admin_url('product-edit.php?id=' . $targetId));
            exit;

        } catch (Exception $e) {
            admin_set_flash('error', 'Error saving product: ' . $e->getMessage());
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?= $isEdit ? 'Edit Product' : 'Add New Product' ?>
    </h1>
    <a href="<?= admin_url('products.php') ?>" class="page-title-action">Back to Products</a>
    <?php if ($isEdit): ?>
        <a href="<?= url('product-details.php?slug=' . urlencode($product['slug'] ?? '')) ?>" target="_blank" class="page-title-action">View Product <i class="bi bi-box-arrow-up-right ms-1"></i></a>
    <?php endif; ?>
    <hr class="wp-header-end">

    <form method="POST" action="" enctype="multipart/form-data" id="post">
        <?= csrf_field() ?>

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2" style="display: flex; gap: 20px; align-items: flex-start;">
                
                <!-- Main Column (Left, ~70%) -->
                <div id="post-body-content" style="flex: 1; min-width: 0;">
                    
                    <!-- Title Div -->
                    <div id="titlediv" style="margin-bottom: 20px;">
                        <div id="titlewrap">
                            <label class="screen-reader-text" for="title">Title</label>
                            <input type="text" name="name" size="30" value="<?= htmlspecialchars($product['name'] ?? '') ?>" id="title" spellcheck="true" autocomplete="off" placeholder="Add product title" required style="width: 100%; font-size: 20px; font-weight: 600; padding: 10px 14px; border: 1px solid #8c8f94; border-radius: 4px; box-sizing: border-box;">
                        </div>
                        <div class="inside" style="margin-top: 6px; font-size: 13px; color: #646970;">
                            <strong>Permalink:</strong> 
                            <?= url('product-details.php?slug=') ?><input type="text" name="slug" value="<?= htmlspecialchars($product['slug'] ?? '') ?>" placeholder="auto-generated-slug" style="font-size: 12px; padding: 2px 6px; border: 1px solid #dcdcde; border-radius: 3px; width: 220px;">
                        </div>
                    </div>

                    <!-- Short Description Metabox -->
                    <div class="postbox" style="margin-bottom: 20px;">
                        <div class="postbox-header">
                            <h2>Short Description</h2>
                        </div>
                        <div class="inside" style="padding: 15px;">
                            <textarea name="short_description" rows="3" style="width: 100%; border: 1px solid #8c8f94; border-radius: 4px; padding: 10px; font-size: 13px; box-sizing: border-box;" placeholder="Brief highlight summary displayed in product cards and sticky summary"><?= htmlspecialchars($product['short_description'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Product Data Metabox (Tabs) -->
                    <div class="postbox" style="margin-bottom: 20px;" id="woocommerce-product-data">
                        <div class="postbox-header" style="border-bottom: 1px solid #c3c4c7; background: #f6f7f7;">
                            <h2 style="font-weight: 600;">Product Data — Standard Audio Equipment</h2>
                        </div>
                        <div class="inside" style="padding: 0;">
                            <div style="display: flex; min-height: 280px;">
                                <!-- Tabs Navigation -->
                                <div style="width: 180px; background: #fafafa; border-right: 1px solid #dcdcde;">
                                    <ul style="list-style: none; margin: 0; padding: 0;" id="prod-tabs-nav">
                                        <li>
                                            <a href="#tab-general" class="prod-tab-link active" onclick="switchProdTab(event, 'tab-general')" style="display: block; padding: 12px 16px; font-size: 13px; font-weight: 600; color: #2c3338; text-decoration: none; border-bottom: 1px solid #eee; border-left: 3px solid var(--wp-primary); background: #fff;">
                                                <i class="bi bi-tag me-2"></i> General
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#tab-inventory" class="prod-tab-link" onclick="switchProdTab(event, 'tab-inventory')" style="display: block; padding: 12px 16px; font-size: 13px; color: #50575e; text-decoration: none; border-bottom: 1px solid #eee; border-left: 3px solid transparent;">
                                                <i class="bi bi-box me-2"></i> Inventory
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#tab-specs" class="prod-tab-link" onclick="switchProdTab(event, 'tab-specs')" style="display: block; padding: 12px 16px; font-size: 13px; color: #50575e; text-decoration: none; border-bottom: 1px solid #eee; border-left: 3px solid transparent;">
                                                <i class="bi bi-sliders me-2"></i> Specifications
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#tab-gallery" class="prod-tab-link" onclick="switchProdTab(event, 'tab-gallery')" style="display: block; padding: 12px 16px; font-size: 13px; color: #50575e; text-decoration: none; border-bottom: 1px solid #eee; border-left: 3px solid transparent;">
                                                <i class="bi bi-images me-2"></i> Gallery
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Tabs Content -->
                                <div style="flex: 1; padding: 20px;" id="prod-tabs-content">
                                    
                                    <!-- TAB 1: General -->
                                    <div id="tab-general" class="prod-tab-panel">
                                        <table class="form-table" style="margin: 0;">
                                            <tr>
                                                <th style="width: 160px;"><label for="sku">SKU (Stock Code)</label></th>
                                                <td>
                                                    <input type="text" name="sku" id="sku" value="<?= htmlspecialchars($product['sku'] ?? '') ?>" class="regular-text" placeholder="e.g. ONS-SPK-15FR">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><label for="model_name">Model Name</label></th>
                                                <td>
                                                    <input type="text" name="model_name" id="model_name" value="<?= htmlspecialchars($product['model_name'] ?? '') ?>" class="regular-text" placeholder="e.g. 15 FR200 Pro">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><label for="price">Regular Price (₹) *</label></th>
                                                <td>
                                                    <input type="number" step="0.01" min="0" name="price" id="price" value="<?= htmlspecialchars($product['price'] ?? '0') ?>" class="regular-text" required>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><label for="compare_at_price">Sale / Compare Price (₹)</label></th>
                                                <td>
                                                    <input type="number" step="0.01" min="0" name="compare_at_price" id="compare_at_price" value="<?= htmlspecialchars($product['compare_at_price'] ?? '') ?>" class="regular-text" placeholder="Optional original MRP">
                                                    <p class="description">Crossed-out original price to show discount badge.</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>

                                    <!-- TAB 2: Inventory -->
                                    <div id="tab-inventory" class="prod-tab-panel" style="display: none;">
                                        <table class="form-table" style="margin: 0;">
                                            <tr>
                                                <th style="width: 160px;"><label for="stock_quantity">Stock Quantity *</label></th>
                                                <td>
                                                    <input type="number" min="0" name="stock_quantity" id="stock_quantity" value="<?= htmlspecialchars($product['stock_quantity'] ?? '10') ?>" class="regular-text" required>
                                                    <p class="description">Units physically available for order booking.</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><label for="stock_status_override">Stock Status</label></th>
                                                <td>
                                                    <select name="stock_status_override" id="stock_status_override">
                                                        <option value="instock" <?= (($product['stock_status'] ?? 'instock') === 'instock') ? 'selected' : '' ?>>In stock</option>
                                                        <option value="outofstock" <?= (($product['stock_status'] ?? '') === 'outofstock') ? 'selected' : '' ?>>Out of stock</option>
                                                    </select>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>

                                    <!-- TAB 3: Specifications -->
                                    <div id="tab-specs" class="prod-tab-panel" style="display: none;">
                                        <p style="margin-top: 0; font-size: 13px; color: #646970;">
                                            Technical parameters shown in the Specifications tab on the storefront.
                                        </p>
                                        <table class="wp-list-table widefat fixed striped" style="margin-bottom: 12px;" id="specs-table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 40%;">Specification Name</th>
                                                    <th style="width: 50%;">Value</th>
                                                    <th style="width: 10%; text-align: center;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="specs-body">
                                                <?php if (!empty($specifications)): ?>
                                                    <?php foreach ($specifications as $spec): ?>
                                                        <tr>
                                                            <td><input type="text" name="spec_keys[]" value="<?= htmlspecialchars($spec['spec_key']) ?>" style="width: 100%;" placeholder="e.g. Nominal Diameter"></td>
                                                            <td><input type="text" name="spec_values[]" value="<?= htmlspecialchars($spec['spec_value']) ?>" style="width: 100%;" placeholder="e.g. 15 inch"></td>
                                                            <td style="text-align: center;"><button type="button" class="button button-small" onclick="this.closest('tr').remove()" style="color: #d63638;"><i class="bi bi-trash"></i></button></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td><input type="text" name="spec_keys[]" value="Model Name" style="width: 100%;"></td>
                                                        <td><input type="text" name="spec_values[]" value="" style="width: 100%;"></td>
                                                        <td style="text-align: center;"><button type="button" class="button button-small" onclick="this.closest('tr').remove()" style="color: #d63638;"><i class="bi bi-trash"></i></button></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" name="spec_keys[]" value="Power Rating" style="width: 100%;"></td>
                                                        <td><input type="text" name="spec_values[]" value="" style="width: 100%;"></td>
                                                        <td style="text-align: center;"><button type="button" class="button button-small" onclick="this.closest('tr').remove()" style="color: #d63638;"><i class="bi bi-trash"></i></button></td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                        <button type="button" class="button button-secondary" onclick="addSpecRow()">
                                            <i class="bi bi-plus-circle me-1"></i> Add Specification
                                        </button>
                                    </div>

                                    <!-- TAB 4: Gallery -->
                                    <div id="tab-gallery" class="prod-tab-panel" style="display: none;">
                                        <p style="margin-top: 0; font-size: 13px; color: #646970;">
                                            Additional gallery images for product thumbnail carousel and full view lightbox.
                                        </p>
                                        
                                        <!-- Fast Upload & Auto-Compress Box -->
                                        <div style="background: #f0f6fc; border: 1.5px dashed #72aee6; border-radius: 6px; padding: 14px 18px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; flex-wrap: wrap; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <i class="bi bi-cloud-arrow-up-fill" style="font-size: 18px; color: #2271b1;"></i>
                                                    <strong style="font-size: 13px;">Upload Gallery Photos</strong>
                                                </div>
                                                <span class="badge" style="background: #e6f4ea; color: #137333; font-size: 11px; padding: 3px 8px; border-radius: 4px; font-weight: 600;">
                                                    <i class="bi bi-lightning-charge-fill me-1"></i> Auto-compressed before upload
                                                </span>
                                            </div>
                                            <p style="margin: 0 0 10px; font-size: 12px; color: #50575e;">
                                                Select one or more images from your computer. Files are scaled to high-resolution ecommerce size (max 1600px) and compressed in your browser before upload to save hosting bandwidth and storage.
                                            </p>
                                            <input type="file" name="gallery_image_files[]" id="gallery_image_files" multiple accept="image/*" style="font-size: 12px;">
                                            <div id="gallery-compress-status" style="margin-top: 8px;"></div>
                                        </div>

                                        <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px; color: #1d2327;">Gallery Image Paths / URLs:</label>
                                        <div id="gallery-container">
                                            <?php if (!empty($galleryImages)): ?>
                                                <?php foreach ($galleryImages as $gImg): ?>
                                                    <div class="gallery-item-row" style="display: flex; gap: 10px; margin-bottom: 8px; align-items: center;">
                                                        <input type="text" name="gallery_images[]" value="<?= htmlspecialchars($gImg['image_path']) ?>" style="flex: 1;" placeholder="assets/images/products/speaker-X.png">
                                                        <button type="button" class="button button-small" onclick="this.closest('.gallery-item-row').remove()" style="color: #d63638;"><i class="bi bi-trash"></i></button>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="gallery-item-row" style="display: flex; gap: 10px; margin-bottom: 8px; align-items: center;">
                                                    <input type="text" name="gallery_images[]" value="" style="flex: 1;" placeholder="assets/images/products/speaker-X.png">
                                                    <button type="button" class="button button-small" onclick="this.closest('.gallery-item-row').remove()" style="color: #d63638;"><i class="bi bi-trash"></i></button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <button type="button" class="button button-secondary" onclick="addGalleryRow()" style="margin-top: 6px;">
                                            <i class="bi bi-plus-circle me-1"></i> Add Gallery Image URL
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Full Description Metabox -->
                    <div class="postbox">
                        <div class="postbox-header">
                            <h2>Full Product Description</h2>
                        </div>
                        <div class="inside" style="padding: 15px;">
                            <textarea name="full_description" rows="10" style="width: 100%; border: 1px solid #8c8f94; border-radius: 4px; padding: 10px; font-size: 13px; box-sizing: border-box;" placeholder="Detailed product specifications, acoustic design, features, and engineering notes"><?= htmlspecialchars($product['full_description'] ?? '') ?></textarea>
                        </div>
                    </div>

                </div>

                <!-- Right Column (Sidebar, ~30%) -->
                <div id="postbox-container-1" style="width: 300px; flex-shrink: 0;">
                    
                    <!-- Publish Box -->
                    <div class="postbox" style="margin-bottom: 20px;">
                        <div class="postbox-header" style="background: #f6f7f7; border-bottom: 1px solid #c3c4c7;">
                            <h2>Publish Status</h2>
                        </div>
                        <div class="inside" style="padding: 15px;">
                            <div style="margin-bottom: 12px; font-size: 13px;">
                                <label style="display: block; font-weight: 600; margin-bottom: 4px;">Status:</label>
                                <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                                    <input type="checkbox" name="is_active" value="1" <?= (!isset($product['is_active']) || $product['is_active'] == 1) ? 'checked' : '' ?>>
                                    <span>Active / Published on Storefront</span>
                                </label>
                            </div>
                            <div style="margin-bottom: 16px; font-size: 13px;">
                                <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                                    <input type="checkbox" name="is_featured" value="1" <?= (!empty($product['is_featured'])) ? 'checked' : '' ?>>
                                    <span style="font-weight: 600; color: #b26b00;"><i class="bi bi-star-fill text-warning me-1"></i> Featured Product</span>
                                </label>
                            </div>
                            <?php if ($isEdit && !empty($product['created_at'])): ?>
                                <div style="font-size: 12px; color: #646970; margin-bottom: 16px; border-top: 1px solid #eee; padding-top: 10px;">
                                    Published on: <strong><?= date('M j, Y @ H:i', strtotime($product['created_at'])) ?></strong>
                                </div>
                            <?php endif; ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #dcdcde; padding-top: 12px;">
                                <?php if ($isEdit): ?>
                                    <a href="<?= admin_url('products.php?action=delete&id=' . $productId) ?>" onclick="return confirm('Permanently delete this product?');" style="color: #d63638; text-decoration: none; font-size: 13px;">Move to Trash</a>
                                <?php else: ?>
                                    <span></span>
                                <?php endif; ?>
                                <button type="submit" class="button button-primary button-large" style="padding: 6px 18px; font-weight: 600;">
                                    <?= $isEdit ? 'Update Product' : 'Publish Product' ?>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Categories Box -->
                    <div class="postbox" style="margin-bottom: 20px;">
                        <div class="postbox-header">
                            <h2>Product Category</h2>
                        </div>
                        <div class="inside" style="padding: 15px;">
                            <select name="category_id" style="width: 100%; padding: 6px 10px; font-size: 13px;">
                                <option value="">— Select Category —</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= (($product['category_id'] ?? 0) == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div style="margin-top: 10px; font-size: 12px;">
                                <a href="<?= admin_url('categories.php') ?>" target="_blank" style="text-decoration: none;">+ Manage Categories</a>
                            </div>
                        </div>
                    </div>

                    <!-- Product Image Box -->
                    <div class="postbox" style="margin-bottom: 20px;">
                        <div class="postbox-header">
                            <h2>Featured Image</h2>
                        </div>
                        <div class="inside" style="padding: 15px;">
                            <?php $currMain = $product['main_image'] ?? 'assets/images/products/speaker-1.png'; ?>
                            <div style="text-align: center; margin-bottom: 12px; background: #fafafa; border: 1px solid #e2e4e7; border-radius: 4px; padding: 10px; min-height: 100px; display: flex; align-items: center; justify-content: center;">
                                <img id="main-image-preview" src="<?= url($currMain) ?>" alt="Product Preview" style="max-height: 140px; max-width: 100%; object-fit: contain;">
                            </div>
                            <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 4px;">Image Path / URL:</label>
                            <input type="text" name="main_image" id="main_image_input" value="<?= htmlspecialchars($currMain) ?>" style="width: 100%; font-size: 12px; margin-bottom: 10px;" oninput="document.getElementById('main-image-preview').src = '<?= url('') ?>' + this.value">
                            <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 4px;">
                                Upload File <span style="font-weight: 400; color: #137333; font-size: 11px;">(Auto-compressed before upload)</span>:
                            </label>
                            <input type="file" name="main_image_file" id="main_image_file" class="auto-compress-file" data-preview="#main-image-preview" data-status="#main-image-status" accept="image/*" style="font-size: 12px; width: 100%;">
                            <div id="main-image-status"></div>
                        </div>
                    </div>

                    <!-- Hover Image Box -->
                    <div class="postbox">
                        <div class="postbox-header">
                            <h2>Hover / Alternate Image</h2>
                        </div>
                        <div class="inside" style="padding: 15px;">
                            <?php $currHover = $product['hover_image'] ?? ''; ?>
                            <div id="hover-image-preview-wrap" style="text-align: center; margin-bottom: 12px; background: #fafafa; border: 1px solid #e2e4e7; border-radius: 4px; padding: 10px; min-height: 80px; display: <?= !empty($currHover) ? 'flex' : 'none' ?>; align-items: center; justify-content: center;">
                                <img id="hover-image-preview" src="<?= !empty($currHover) ? url($currHover) : '' ?>" alt="Hover Preview" style="max-height: 120px; max-width: 100%; object-fit: contain;">
                            </div>
                            <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 4px;">Hover Image Path / URL:</label>
                            <input type="text" name="hover_image" id="hover_image_input" value="<?= htmlspecialchars($currHover) ?>" placeholder="assets/images/products/speaker-hover.png" style="width: 100%; font-size: 12px; margin-bottom: 10px;" oninput="var el = document.getElementById('hover-image-preview'); el.src = '<?= url('') ?>' + this.value; document.getElementById('hover-image-preview-wrap').style.display = this.value ? 'flex' : 'none';">
                            <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 4px;">
                                Upload Hover File <span style="font-weight: 400; color: #137333; font-size: 11px;">(Auto-compressed before upload)</span>:
                            </label>
                            <input type="file" name="hover_image_file" id="hover_image_file" class="auto-compress-file" data-preview="#hover-image-preview" data-preview-wrap="#hover-image-preview-wrap" data-status="#hover-image-status" accept="image/*" style="font-size: 12px; width: 100%;">
                            <div id="hover-image-status"></div>
                            <p class="description" style="margin-top: 6px;">Shown on product card hover.</p>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </form>
</div>

<style>
@keyframes spin { 100% { transform: rotate(360deg); } }
</style>
<script>
function switchProdTab(e, tabId) {
    e.preventDefault();
    document.querySelectorAll('.prod-tab-link').forEach(function(link) {
        link.style.borderLeft = '3px solid transparent';
        link.style.background = 'transparent';
        link.style.fontWeight = 'normal';
    });
    e.currentTarget.style.borderLeft = '3px solid var(--wp-primary)';
    e.currentTarget.style.background = '#fff';
    e.currentTarget.style.fontWeight = '600';

    document.querySelectorAll('.prod-tab-panel').forEach(function(panel) {
        panel.style.display = 'none';
    });
    var target = document.getElementById(tabId);
    if (target) target.style.display = 'block';
}

function addSpecRow() {
    var tbody = document.getElementById('specs-body');
    var tr = document.createElement('tr');
    tr.innerHTML = '<td><input type="text" name="spec_keys[]" placeholder="Specification name" style="width: 100%;"></td>' +
                   '<td><input type="text" name="spec_values[]" placeholder="Value" style="width: 100%;"></td>' +
                   '<td style="text-align: center;"><button type="button" class="button button-small" onclick="this.closest(\'tr\').remove()" style="color: #d63638;"><i class="bi bi-trash"></i></button></td>';
    tbody.appendChild(tr);
}

function addGalleryRow() {
    var container = document.getElementById('gallery-container');
    var div = document.createElement('div');
    div.className = 'gallery-item-row';
    div.style.cssText = 'display: flex; gap: 10px; margin-bottom: 8px; align-items: center;';
    div.innerHTML = '<input type="text" name="gallery_images[]" placeholder="assets/images/products/speaker-X.png" style="flex: 1;">' +
                    '<button type="button" class="button button-small" onclick="this.closest(\'.gallery-item-row\').remove()" style="color: #d63638;"><i class="bi bi-trash"></i></button>';
    container.appendChild(div);
}

// --- Client-side Image Compression Engine (Compresses before upload on hosting) ---
var pendingCompressions = 0;

function formatBytes(bytes) {
    if (!bytes || bytes === 0) return '0 B';
    var k = 1024;
    var sizes = ['B', 'KB', 'MB', 'GB'];
    var i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

/**
 * Compresses an image file client-side using HTML5 Canvas.
 * - Scales down to maxWidth/maxHeight (default 1600x1600).
 * - Preserves full PNG alpha transparency.
 * - Compresses JPEG/WebP with 82% quality.
 * - Returns a new File object with reduced filesize.
 */
function compressImage(file, maxDim, quality) {
    if (typeof maxDim === 'undefined') maxDim = 1600;
    if (typeof quality === 'undefined') quality = 0.82;

    return new Promise(function(resolve) {
        if (!file || !file.type || !file.type.startsWith('image/')) {
            resolve({ file: file, originalSize: file ? file.size : 0, compressedSize: file ? file.size : 0, savedPercent: 0 });
            return;
        }
        if (file.type === 'image/svg+xml') {
            resolve({ file: file, originalSize: file.size, compressedSize: file.size, savedPercent: 0 });
            return;
        }

        var reader = new FileReader();
        reader.onload = function(e) {
            var img = new Image();
            img.onload = function() {
                var origW = img.width;
                var origH = img.height;
                var targetW = origW;
                var targetH = origH;

                if (origW > maxDim || origH > maxDim) {
                    if (origW > origH) {
                        targetW = maxDim;
                        targetH = Math.round((origH * maxDim) / origW);
                    } else {
                        targetH = maxDim;
                        targetW = Math.round((origW * maxDim) / origH);
                    }
                }

                var canvas = document.createElement('canvas');
                canvas.width = targetW;
                canvas.height = targetH;
                var ctx = canvas.getContext('2d');
                ctx.imageSmoothingEnabled = true;
                ctx.imageSmoothingQuality = 'high';

                var isPng = (file.type === 'image/png');
                if (isPng) {
                    ctx.clearRect(0, 0, targetW, targetH);
                }

                ctx.drawImage(img, 0, 0, targetW, targetH);

                var mimeType = isPng ? 'image/png' : (file.type === 'image/webp' ? 'image/webp' : 'image/jpeg');
                var outQuality = isPng ? undefined : quality;

                canvas.toBlob(function(blob) {
                    if (blob && blob.size < file.size) {
                        var compressedFile = new File([blob], file.name, {
                            type: mimeType,
                            lastModified: Date.now()
                        });
                        var savedPct = Math.round((1 - (blob.size / file.size)) * 100);
                        resolve({
                            file: compressedFile,
                            originalSize: file.size,
                            compressedSize: blob.size,
                            savedPercent: savedPct,
                            previewUrl: URL.createObjectURL(blob)
                        });
                    } else {
                        resolve({
                            file: file,
                            originalSize: file.size,
                            compressedSize: file.size,
                            savedPercent: 0,
                            previewUrl: e.target.result
                        });
                    }
                }, mimeType, outQuality);
            };
            img.onerror = function() {
                resolve({ file: file, originalSize: file.size, compressedSize: file.size, savedPercent: 0 });
            };
            img.src = e.target.result;
        };
        reader.onerror = function() {
            resolve({ file: file, originalSize: file.size, compressedSize: file.size, savedPercent: 0 });
        };
        reader.readAsDataURL(file);
    });
}

// Single image inputs (Main and Hover)
document.querySelectorAll('.auto-compress-file').forEach(function(input) {
    input.addEventListener('change', async function() {
        if (!this.files || this.files.length === 0) return;
        var file = this.files[0];
        var statusEl = document.querySelector(this.dataset.status);
        var previewEl = document.querySelector(this.dataset.preview);
        var previewWrap = this.dataset.previewWrap ? document.querySelector(this.dataset.previewWrap) : null;

        if (statusEl) {
            statusEl.innerHTML = '<span style="color: #2271b1; font-size: 11px;"><i class="bi bi-arrow-repeat me-1" style="animation: spin 1s infinite linear; display: inline-block;"></i> Compressing image before upload...</span>';
        }

        pendingCompressions++;
        try {
            var res = await compressImage(file);
            var dt = new DataTransfer();
            dt.items.add(res.file);
            this.files = dt.files;

            if (previewEl && res.previewUrl) {
                previewEl.src = res.previewUrl;
                if (previewWrap) previewWrap.style.display = 'flex';
            }

            if (statusEl) {
                if (res.savedPercent > 0) {
                    statusEl.innerHTML = '<div style="background: #e6f4ea; border: 1px solid #b7e1cd; color: #137333; border-radius: 4px; padding: 4px 8px; font-size: 11px; margin-top: 5px; display: flex; align-items: center; gap: 5px;">' +
                        '<i class="bi bi-check-circle-fill" style="color: #137333;"></i>' +
                        '<span><strong>Compressed before upload:</strong> ' + formatBytes(res.originalSize) + ' &rarr; ' + formatBytes(res.compressedSize) + ' <strong style="color: #0d652d;">(' + res.savedPercent + '% saved)</strong></span>' +
                        '</div>';
                } else {
                    statusEl.innerHTML = '<div style="background: #f1f3f4; border: 1px solid #dadce0; color: #3c4043; border-radius: 4px; padding: 4px 8px; font-size: 11px; margin-top: 5px;">' +
                        '<i class="bi bi-info-circle me-1"></i> Already optimized (' + formatBytes(res.originalSize) + ')' +
                        '</div>';
                }
            }
        } catch (err) {
            console.error('Compression error:', err);
            if (statusEl) statusEl.innerHTML = '';
        } finally {
            pendingCompressions--;
        }
    });
});

// Gallery multiple images input
var galleryInput = document.getElementById('gallery_image_files');
if (galleryInput) {
    galleryInput.addEventListener('change', async function() {
        if (!this.files || this.files.length === 0) return;
        var statusEl = document.getElementById('gallery-compress-status');
        var filesCount = this.files.length;

        if (statusEl) {
            statusEl.innerHTML = '<span style="color: #2271b1; font-size: 11px;"><i class="bi bi-arrow-repeat me-1" style="animation: spin 1s infinite linear; display: inline-block;"></i> Compressing ' + filesCount + ' gallery image(s) before upload...</span>';
        }

        pendingCompressions++;
        try {
            var dt = new DataTransfer();
            var totalOrig = 0;
            var totalComp = 0;
            var previewsHtml = '<div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px;">';

            for (var i = 0; i < this.files.length; i++) {
                var f = this.files[i];
                var res = await compressImage(f);
                dt.items.add(res.file);
                totalOrig += res.originalSize;
                totalComp += res.compressedSize;
                previewsHtml += '<div style="background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; padding: 4px; text-align: center; width: 75px;">' +
                    '<img src="' + (res.previewUrl || '') + '" style="width: 65px; height: 50px; object-fit: contain; display: block; margin: 0 auto 3px;">' +
                    '<span style="font-size: 9px; color: #137333; font-weight: 600; display: block;">' + formatBytes(res.compressedSize) + '</span>' +
                    '</div>';
            }
            previewsHtml += '</div>';

            this.files = dt.files;
            var overallSaved = totalOrig > 0 ? Math.round((1 - (totalComp / totalOrig)) * 100) : 0;

            if (statusEl) {
                statusEl.innerHTML = '<div style="background: #e6f4ea; border: 1px solid #b7e1cd; color: #137333; border-radius: 4px; padding: 6px 10px; font-size: 11px;">' +
                    '<div style="font-weight: 600;"><i class="bi bi-check-circle-fill me-1"></i> ' + filesCount + ' gallery image(s) compressed before upload!</div>' +
                    '<div style="margin-top: 2px;">Total size: ' + formatBytes(totalOrig) + ' &rarr; <strong>' + formatBytes(totalComp) + '</strong> (' + overallSaved + '% bandwidth saved)</div>' +
                    previewsHtml +
                    '</div>';
            }
        } catch (err) {
            console.error('Gallery compression error:', err);
            if (statusEl) statusEl.innerHTML = '';
        } finally {
            pendingCompressions--;
        }
    });
}

// Form submit guard
var productForm = document.getElementById('post');
if (productForm) {
    productForm.addEventListener('submit', function(e) {
        if (pendingCompressions > 0) {
            e.preventDefault();
            alert('Images are currently being compressed. Please wait a moment for compression to finish.');
        }
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
