 public function up(): void
 {
 Schema::create('users', function (Blueprint $table) {
 $table->id();
 $table->string('name');
 $table->string('email')->unique();
 $table->timestamp('email_verified_at')->nullable();
 $table->string('password');
 $table->rememberToken();
 $table->timestamps();
 });



 Schema::table('users', function (Blueprint $table) {
 $table->enum('role', ['admin', 'customer', 'manager', 'staff'])->default('customer');
 });



 /**
 * Reverse the migrations.
 */
 public function down(): void
 {
 Schema::table('users', function (Blueprint $table) {
 $table->enum('role', ['admin', 'customer', 'manager', 'staff'])
 ->default('customer')
 ->change();
 });
 }



 Schema::create('profiles', function (Blueprint $table) {
 $table->id();
 $table->foreignId('user_id')->constrained()->onDelete('cascade');
 $table->string('phone')->nullable();
 $table->text('address')->nullable();
 $table->string('avatar')->nullable();
 $table->timestamps();
 });

 Schema::create('categories', function (Blueprint $table) {
 $table->id();
 $table->string('name');
 $table->timestamps();
 });



 Schema::create('products', function (Blueprint $table) {
 $table->id();
 $table->foreignId('category_id')->constrained();
 $table->string('name');
 $table->text('description')->nullable();
 $table->decimal('price', 10, 2);
 $table->integer('quantity');
 $table->string('image')->nullable();
 $table->timestamps();
 });

 Schema::create('stores', function (Blueprint $table) {
 $table->id();
 $table->string('name'); // Store name: "Shopno Dhaka"
 $table->string('city')->nullable();
 $table->string('address')->nullable();
 $table->timestamps();
 });




 Schema::create('store_user', function (Blueprint $table) {
 $table->id();
 $table->foreignId('store_id')->constrained()->onDelete('cascade');
 $table->foreignId('user_id')->constrained()->onDelete('cascade');
 $table->enum('role', ['manager', 'staff'])->default('staff'); // store-specific
 $table->timestamps();
 });


 Schema::create('store_product', function (Blueprint $table) {
 $table->id();
 $table->foreignId('store_id')->constrained()->onDelete('cascade');
 $table->foreignId('product_id')->constrained()->onDelete('cascade');
 $table->integer('quantity')->default(0);
 $table->timestamps();
 });



 Schema::create('orders', function (Blueprint $table) {
 $table->id();
 $table->foreignId('user_id')->constrained()->onDelete('cascade');
 $table->foreignId('store_id')->constrained()->onDelete('cascade'); // added store_id
 $table->string('status')->default('pending');
 $table->decimal('total', 10, 2);
 $table->timestamps();
 });



 Schema::create('order_details', function (Blueprint $table) {
 $table->id();
 $table->foreignId('order_id')->constrained()->onDelete('cascade');
 $table->foreignId('product_id')->constrained()->onDelete('cascade');
 $table->foreignId('store_id')->constrained()->onDelete('cascade');
 $table->integer('quantity'); // required, must be set when creating
 $table->decimal('price', 10, 2); // unit price, required
 $table->decimal('total_amount', 10, 2); // quantity * price, required
 $table->timestamps();
 });



 Schema::create('invoices', function (Blueprint $table) {
 $table->id(); // Primary key

 // Relationships

 $table->foreignId('customer_id')->constrained('users')->onDelete('restrict');
 $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
 $table->foreignId('store_id')->constrained()->onDelete('cascade');
 $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');

 // Invoice info
 $table->string('invoice_number')->unique();
 $table->date('invoice_date');

 // Amounts in proper logical order
 $table->decimal('total_amount', 10, 2); // Sum of product prices
 $table->decimal('discount_amount', 10, 2); // Discount applied
 $table->decimal('subtotal_amount', 10, 2); // total_amount - discount_amount
 $table->decimal('vat_percentage', 5, 2); // e.g., 5, 7.5, 15
 $table->decimal('vat_amount', 10, 2); // subtotal_amount * vat_percentage / 100
 $table->decimal('final_total', 10, 2); // subtotal_amount + vat_amount

 // Optional fields
 $table->text('notes')->nullable();
 $table->string('status')->default('Pending');

 $table->timestamps();
 });




 Schema::create('invoice_details', function (Blueprint $table) {
 $table->id();

 // Relationships
 $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
 $table->foreignId('product_id')->constrained()->onDelete('restrict'); // don’t delete products by accident

 // Product info
 $table->integer('quantity'); // Number of units sold
 $table->decimal('unit_price', 10, 2); // Price per unit
 $table->decimal('total_amount', 10, 2); // quantity * unit_price

 // Discounts and taxes (optional if needed per product)
 $table->decimal('discount_amount', 10, 2)->default(0); // discount applied per product
 $table->decimal('subtotal_amount', 10, 2); // total_amount - discount_amount
 $table->decimal('vat_percentage', 5, 2)->default(0); // e.g., 5, 7.5, 15
 $table->decimal('vat_amount', 10, 2); // subtotal_amount * vat_percentage / 100
 $table->decimal('final_total', 10, 2); // subtotal_amount + vat_amount

 $table->timestamps();
 });
 php artisan make:migration create_orders_table
 php artisan make:migration create_order_details_table
 php artisan make:migration create_invoices_table
 php artisan make:migration create_invoice_details_table

 php artisan db:seed --class=StoreSeeder
