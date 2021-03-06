<?php
define('_TOKEN_ERROR', '照合用のワンタイム・チケットが見つかりませんでした。ほとんどの場合は操作手順の関係でワンタイム・チケットが消費されただけですが、CSRF攻撃を受けた可能性もあります（この操作は本当にあなたが望んだ操作ですか？）　操作内容をしっかり確認し、もう一度操作を行ってください。');
define('_SYSTEM_MODULE_ERROR', '以下のモジュールが導入されていません');
define('_INSTALL','インストール');
define('_UNINSTALL','削除');
define('_SYS_MODULE_UNINSTALLED','必須(未導入)');
define('_SYS_MODULE_DISABLED','必須(無効)');
define('_SYS_RECOMMENDED_MODULES','導入推奨');
define('_SYS_OPTION_MODULES','選択導入可能');
define('_UNINSTALL_CONFIRM','必須モジュールのアンインストールを行いますか？');
define('_PLEASEWAIT','しばらくお待ちください');
define('_FETCHING','Loading...');
define('_TAKINGBACK','元の場所へと戻ります....');
define('_LOGOUT','ログアウト');
define('_SUBJECT','表題');
define('_MESSAGEICON','アイコン');
define('_COMMENTS','コメント');
define('_POSTANON','匿名で投稿');
define('_DISABLESMILEY','顔アイコンを無効');
define('_DISABLEHTML','HTMLタグを無効');
define('_PREVIEW','プレビュー');
define('_GO','送信');
define('_NESTED','ネスト表示');
define('_NOCOMMENTS','コメント非表示');
define('_FLAT','フラット表示');
define('_THREADED','スレッド表示');
define('_OLDESTFIRST','古いものから');
define('_NEWESTFIRST','新しいものから');
define('_MORE','もっと...');
define("_MULTIPAGE","[pagebreak]タグを本文内に記入することでページ区切りを挿入することができます。");
define('_IFNOTRELOAD','ページが自動的に更新されない場合は<a href="%s">ここ</a>をクリックしてください');
define('_WARNINSTALL2','注意：ファイル%sがサーバ上に存在します。インストール完了後は必ず削除してください。');
define('_WARNINWRITEABLE','注意：ファイル%sへの書き込みが可能となっています。このファイルのパーミッション設定を変更してください。');
define('_WARNPHPENV','注意：PHPの設定環境の中で、"%s" が "%s"になっています。%s');
define('_WARNSECURITY','（サイトの脆弱につながる危険性があります。）');
define('_POSTEDBY','投稿者：'); 
define('_PROFILE','プロフィール');
define('_VISITWEBSITE','ホームページ');
define('_SENDPMTO','%sさんにプライベートメッセージを送る。');
define('_SENDEMAILTO','%sさんにメールを送る。');
define('_ADD','追加');
define('_REPLY','返信');
define('_DATE','投稿日時：');
define('_MAIN','トップ');
define('_MANUAL','マニュアル');
define('_INFO','バージョン情報');
define('_CPHOME','管理メニュー');
define('_YOURHOME','ホームページ');
define('_WHOSONLINE','オンライン状況'); 
define('_GUESTS', 'ゲスト');
define('_MEMBERS', '登録ユーザ');
define('_ONLINEPHRASE','%s 人のユーザが現在オンラインです。');
define('_ONLINEPHRASEX','%s 人のユーザが %s を参照しています。');
define('_CLOSE','閉じる');  
define('_QUOTEC','引用：');
define('_NOPERM','このエリアへのアクセスは許可されていません。');
define('_NO','いいえ');
define('_YES','はい');
define('_EDIT','編集');
define('_DELETE','削除');
define('_VIEW','閲覧');
define('_SUBMIT','送信');
define('_MODULENOEXIST','選択されたページは存在しません');
define('_ALIGN','位置');
define('_LEFT','左');
define('_CENTER','中央');
define('_RIGHT','右');
define('_FORM_ENTER', '%sを入力してください');
define('_MUSTWABLE','ファイル %s への書き込み権限があるかどうか確認してください。');
define('_PREFERENCES', '一般設定');
define('_VERSION', 'バージョン');
define('_DESCRIPTION', '説明');
define('_ERRORS', 'エラー');
define('_NONE', 'なし');
define('_ON','投稿日時：');
define('_READS','ヒット');
define('_WELCOMETO','%sへようこそ');
define('_SEARCH','検索');
define('_ALL', 'すべて');
define('_TITLE', '題名');      
define('_OPTIONS', 'オプション');
define('_QUOTE', '引用');     
define('_LIST', '一覧');
define('_LOGIN','ログイン');
define('_USERNAME','ユーザ名: ');
define('_PASSWORD','パスワード: ');
define('_SELECT','選択');
define('_IMAGE','画像');
define('_SEND','送信');
define('_CANCEL','キャンセル');
define('_ASCENDING','昇順');
define('_DESCENDING','降順');
define('_BACK', '戻る');
define('_NOTITLE', '無題');
define('_RETURN_TOP', 'Topへ戻る');
define('_IMGMANAGER','イメージ・マネジャー');
define('_NUMIMAGES', '%s 枚');
define('_ADDIMAGE','画像ファイルの追加');
define('_IMAGENAME','画像名:');
define('_IMGMAXSIZE','アップロードを許可するファイルサイズ(バイト数):');
define('_IMGMAXWIDTH','アップロードを許可する画像の横幅（ピクセル数）:');
define('_IMGMAXHEIGHT','アップロードを許可する画像の高さ（ピクセル数）:');
define('_IMAGECAT','カテゴリ:');
define('_IMAGEFILE','画像ファイル名:');
define('_IMGWEIGHT','イメージマネジャーでの表示順:');
define('_IMGDISPLAY','この画像を表示する');
define('_IMAGEMIME','MIMEタイプ:');
define('_FAILFETCHIMG', 'アップロードファイル %s が取得できませんでした。');
define('_FAILSAVEIMG', '画像ファイル %s をデータベースに格納できませんでした。');
define('_NOCACHE', 'キャッシュなし');
define('_CLONE', '複製');
define('_STARTSWITH', '前方一致');
define('_ENDSWITH', '後方一致');
define('_MATCHES', '完全一致');
define('_CONTAINS', '次の単語を含む');
define('_REGISTER','登録');
define('_SIZE','大きさ');  
define('_FONT','フォント');  
define('_COLOR','色');  
define('_EXAMPLE','サンプル');
define('_ENTERURL','リンクしたいサイトのURLを入力してください。');
define('_ENTERWEBTITLE','サイト名を入力してください。');
define('_ENTERIMGURL','画像ファイルのURLを入力してください。');
define('_ENTERIMGPOS','画像ファイルの配置を決めてください。');
define('_IMGPOSRORL','「R」または「r」を入力すると右側に、「L」または「l」を入力すると左側に表示されます。指定しない場合は空欄にしてください。');
define('_ERRORIMGPOS','入力が正しくありません。画像ファイルの配置を決めてください。');
define('_ENTEREMAIL','リンクしたいメールアドレスを入力してください。');
define('_ENTERCODE','プログラムコードを入力してください。');
define('_ENTERQUOTE','引用したい文を入力してください。');
define('_ENTERTEXTBOX','テキストボックスに入力してください。');
define('_ALLOWEDCHAR','最大バイト数：');
define('_CURRCHAR','現在のバイト数：');
define('_PLZCOMPLETE','表題およびメッセージ文を記入してください。');
define('_MESSAGETOOLONG','メッセージ文が長すぎます。');
define("_DATESTRING","Y-n-j G:i:s");
define("_MEDIUMDATESTRING","Y-n-j G:i");
define("_SHORTDATESTRING","Y-n-j");
define('_SECOND', '1秒');
define('_SECONDS', '%s秒');
define('_MINUTE', '1分');
define('_MINUTES', '%s分');
define('_HOUR', '1時間');
define('_HOURS', '%s時間');
define('_DAY', '1日');
define('_DAYS', '%s日');
define('_WEEK', '1週間');
define('_MONTH', '1ヶ月');
define('_HELP', "ヘルプ");
@define('_CHARSET', 'UTF-8');
@define('_LANGCODE', 'ja');
mb_language( 'ja' ) ;
@ini_set('default_charset', _CHARSET);
define("_MBSTRING_LANGUAGE", "japanese");
define('XOOPS_USE_MULTIBYTES', '1');
if ( class_exists( 'XCube_Root' ) && function_exists('mb_convert_encoding') && function_exists('mb_convert_kana')) {
	$root =& XCube_Root::getSingleton();
	$root->mDelegateManager->add('Legacy_Mailer.ConvertLocal', 'Legacy_JapaneseUtf8_convLocal');
}
@define('LEGACY_MAIL_LANG','ja');
@define('LEGACY_MAIL_CHAR','iso-2022-jp');
@define('LEGACY_MAIL_ENCO','7bit');
if( ! defined( 'FOR_XOOPS_LANG_CHECKER' ) ) {
function Legacy_JapaneseUtf8_convLocal(&$text, $mime)
{
	if ($mime) {
		switch ($mime) {
			case '1':
				$text = mb_encode_mimeheader($text, LEGACY_MAIL_CHAR, 'B', "\n");
				break;
			case '2':
				$text = mb_encode_mimeheader($text, LEGACY_MAIL_CHAR, 'B', "");
				break;
		}
	}
	else {
		$text = mb_convert_encoding($text, 'JIS', _CHARSET);
	}
}
function xoops_language_trim($text)
{
	if (function_exists('mb_convert_kana')) {
		$text = mb_convert_kana($text, 's');
	}
	$text = trim($text);
	return $text;
}
}
?>
