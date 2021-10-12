<?php if( !route(3) ): ?>
<div class="col-md-8">
  <div class="settings-header__table">
    <a href="<?php echo site_url("admin/settings/language/new") ?>"  class="btn btn-default m-b">New language option</a>
  </div>
  <hr>
   <table class="table report-table" style="border:1px solid #ddd">
      <thead>
         <tr>
            <th>Language Name</th>
            <th></th>
         </tr>
      </thead>
      <tbody>
         <?php foreach($languageList as $language): ?>
         <tr class="<?php if( $language["language_type"] == 1 ): echo 'grey'; endif; ?>">
            <td> <?php echo $language["language_name"]; if( $language["default_language"] == 1 ): echo ' <span class="badge">Default</span>'; endif; ?> </td>
            <td class="text-right col-md-1">
              <div class="dropdown pull-right">
                <button type="button" class="btn btn-default btn-xs dropdown-toggle btn-xs-caret" data-toggle="dropdown">Options <span class="caret"></span></button>
                <ul class="dropdown-menu">
                  <?php if( countRow(["table"=>"languages","where"=>["language_type"=>"2"]]) > 1 && $language["language_type"] == 2 ): ?>
                    <li>
                      <a href="<?php echo site_url('admin/settings/language/?lang-id='.$language["language_code"].'&lang-type=1') ?>">
                        Deactivate
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if( $language["language_type"] == 1 ): ?>
                    <li>
                      <a href="<?php echo site_url('admin/settings/language/?lang-id='.$language["language_code"].'&lang-type=2') ?>">
                        Activate
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if( $language["default_language"] == 0 ): ?>
                    <li>
                      <a href="<?php echo site_url('admin/settings/language/?lang-id='.$language["language_code"].'&lang-default=1') ?>">
                        Make Default Language
                      </a>
                    </li>
                  <?php endif; ?>
                  <li>
                    <a href="<?php echo site_url('admin/settings/language/'.$language["language_code"]) ?>">
                    Edit
                    </a>
                  </li>
                </ul>
              </div>
            </td>
         </tr>
         <?php endforeach; ?>
      </tbody>
   </table>
</div>
<?php elseif( route(3) == "new" ): ?>
<div class="col-md-8">
   <div class="panel panel-default">
      <div class="panel-body">
        <?php if( $error ): ?>
          <div class="alert alert-danger "><?php echo $errorText; ?></div>
        <?php endif; ?>
         <form action="<?php echo site_url('admin/settings/language/new') ?>" method="post" enctype="multipart/form-data">
           <div class="form-group">
              <label class="control-label">Language Name</label>
              <input type="text" class="form-control" name="language">
           </div>
           <div class="form-group">
              <label class="control-label">Language Code</label>
              <select class="form-control" name="languagecode">
                 <option value="ar">ar (Arabic)</option>
                 <option value="af">af (Afrikaans)</option>
                 <option value="am">am (Amharic)</option>
                 <option value="sq">sq (Albanian)</option>
                 <option value="hy">hy (Armenian)</option>
                 <option value="az">az (Azerbaijani)</option>
                 <option value="eu">eu (Basque)</option>
                 <option value="bn">bn (Bengali)</option>
                 <option value="bg">bg (Bulgarian)</option>
                 <option value="ca">ca (Catalan)</option>
                 <option value="zh-HK">zh-HK (Chinese Hong Kong)</option>
                 <option value="zh-CN">zh-CN (Chinese Simplified)</option>
                 <option value="zh-TW">zh-TW (Chinese Traditional)</option>
                 <option value="hr">hr (Croatian)</option>
                 <option value="cs">cs (Czech)</option>
                 <option value="da">da (Danish)</option>
                 <option value="nl">nl (Dutch)</option>
                 <option value="en-GB">en-GB (English UK)</option>
                 <option value="en">en (English US)</option>
                 <option value="et">et (Estonian)</option>
                 <option value="fil">fil (Filipino)</option>
                 <option value="fi">fi (Finnish)</option>
                 <option value="fr">fr (French)</option>
                 <option value="fr-CA">fr-CA (French Canadian)</option>
                 <option value="gl">gl (Galician)</option>
                 <option value="ka">ka (Georgian)</option>
                 <option value="de">de (German)</option>
                 <option value="de-AT">de-AT (German Austria)</option>
                 <option value="de-CH">de-CH (German Switzerland)</option>
                 <option value="el">el (Greek)</option>
                 <option value="gu">gu (Gujarati)</option>
                 <option value="iw">iw (Hebrew)</option>
                 <option value="hi">hi (Hindi)</option>
                 <option value="hu">hu (Hungarain)</option>
                 <option value="is">is (Icelandic)</option>
                 <option value="id">id (Indonesian)</option>
                 <option value="it">it (Italian)</option>
                 <option value="ja">ja (Japanese)</option>
                 <option value="kn">kn (Kannada)</option>
                 <option value="ko">ko (Korean)</option>
                 <option value="lo">lo (Laothian)</option>
                 <option value="lv">lv (Latvian)</option>
                 <option value="lt">lt (Lithuanian)</option>
                 <option value="ms">ms (Malay)</option>
                 <option value="ml">ml (Malayalam)</option>
                 <option value="mr">mr (Marathi)</option>
                 <option value="mn">mn (Mongolian)</option>
                 <option value="no">no (Norwegian)</option>
                 <option value="fa">fa (Persian)</option>
                 <option value="pl">pl (Polish)</option>
                 <option value="pt">pt (Portuguese)</option>
                 <option value="pt-BR">pt-BR (Portuguese Brazil)</option>
                 <option value="pt-PT">pt-PT (Portuguese Portugal)</option>
                 <option value="ro">ro (Romanian)</option>
                 <option value="ru">ru (Russian)</option>
                 <option value="sr">sr (Serbian)</option>
                 <option value="si">si (Sinhalese)</option>
                 <option value="sk">sk (Slovak)</option>
                 <option value="sl">sl (Slovenian)</option>
                 <option value="es">es (Spanish)</option>
                 <option value="es-419">es-419 (Spanish Latin America)</option>
                 <option value="sw">sw (Swahili)</option>
                 <option value="sv">sv (Swedish)</option>
                 <option value="ta">ta (Tamil)</option>
                 <option value="te">te (Telugu)</option>
                 <option value="th">th (Thai)</option>
                 <option value="tr">tr (Turkish)</option>
                 <option value="uk">uk (Ukrainian)</option>
                 <option value="ur">ur (Urdu)</option>
                 <option value="vi">vi (Vietnamese)</option>
                 <option value="zu">zu (Zulu)</option>
              </select>
           </div>
           <hr>
            <?php foreach( $languageArray as $key => $val ): ?>
              <div class="form-group">
                 <label class="control-label"><?php echo $key; ?></label>
                 <input type="text" class="form-control" name="Language[<?php echo $key; ?>]" value="<?php echo $val;?>">
              </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary">Ayarları Güncelle</button>
         </form>
      </div>
   </div>
</div>
<?php elseif( route(3) ): ?>
<div class="col-md-8">
   <div class="panel panel-default">
      <div class="panel-body">
         <form action="<?php echo site_url('admin/settings/language/'.route(3)) ?>" method="post" enctype="multipart/form-data">
           <div class="form-group">
              <label class="control-label">Language Name</label>
              <input type="text" class="form-control" name="language" value="<?php echo $language["language_name"] ?>">
           </div>
           <div class="form-group">
              <label class="control-label">Language Code</label>
              <select class="form-control" name="languagecode">
                 <option value="ar" <?php if( $language["language_code"] == "ar" ): echo 'selected'; endif; ?>>ar (Arabic)</option>
                 <option value="af" <?php if( $language["language_code"] == "af" ): echo 'selected'; endif; ?>>af (Afrikaans)</option>
                 <option value="am" <?php if( $language["language_code"] == "am" ): echo 'selected'; endif; ?>>am (Amharic)</option>
                 <option value="sq" <?php if( $language["language_code"] == "sq" ): echo 'selected'; endif; ?>>sq (Albanian)</option>
                 <option value="hy" <?php if( $language["language_code"] == "hy" ): echo 'selected'; endif; ?>>hy (Armenian)</option>
                 <option value="az" <?php if( $language["language_code"] == "az" ): echo 'selected'; endif; ?>>az (Azerbaijani)</option>
                 <option value="eu" <?php if( $language["language_code"] == "eu" ): echo 'selected'; endif; ?>>eu (Basque)</option>
                 <option value="bn" <?php if( $language["language_code"] == "bn" ): echo 'selected'; endif; ?>>bn (Bengali)</option>
                 <option value="bg" <?php if( $language["language_code"] == "bg" ): echo 'selected'; endif; ?>>bg (Bulgarian)</option>
                 <option value="ca" <?php if( $language["language_code"] == "ca" ): echo 'selected'; endif; ?>>ca (Catalan)</option>
                 <option value="zh-HK" <?php if( $language["language_code"] == "zh-HK" ): echo 'selected'; endif; ?>>zh-HK (Chinese Hong Kong)</option>
                 <option value="zh-CN" <?php if( $language["language_code"] == "zh-CN" ): echo 'selected'; endif; ?>>zh-CN (Chinese Simplified)</option>
                 <option value="zh-TW" <?php if( $language["language_code"] == "zh-TW" ): echo 'selected'; endif; ?>>zh-TW (Chinese Traditional)</option>
                 <option value="hr" <?php if( $language["language_code"] == "hr" ): echo 'selected'; endif; ?>>hr (Croatian)</option>
                 <option value="cs" <?php if( $language["language_code"] == "cs" ): echo 'selected'; endif; ?>>cs (Czech)</option>
                 <option value="da" <?php if( $language["language_code"] == "da" ): echo 'selected'; endif; ?>>da (Danish)</option>
                 <option value="nl" <?php if( $language["language_code"] == "nl" ): echo 'selected'; endif; ?>>nl (Dutch)</option>
                 <option value="en-GB" <?php if( $language["language_code"] == "en-GB" ): echo 'selected'; endif; ?>>en-GB (English UK)</option>
                 <option value="en" <?php if( $language["language_code"] == "en" ): echo 'selected'; endif; ?>>en (English US)</option>
                 <option value="et" <?php if( $language["language_code"] == "et" ): echo 'selected'; endif; ?>>et (Estonian)</option>
                 <option value="fil" <?php if( $language["language_code"] == "fil" ): echo 'selected'; endif; ?>>fil (Filipino)</option>
                 <option value="fi" <?php if( $language["language_code"] == "fi" ): echo 'selected'; endif; ?>>fi (Finnish)</option>
                 <option value="fr" <?php if( $language["language_code"] == "fr" ): echo 'selected'; endif; ?>>fr (French)</option>
                 <option value="fr-CA" <?php if( $language["language_code"] == "fr-CA" ): echo 'selected'; endif; ?>>fr-CA (French Canadian)</option>
                 <option value="gl" <?php if( $language["language_code"] == "gl" ): echo 'selected'; endif; ?>>gl (Galician)</option>
                 <option value="ka" <?php if( $language["language_code"] == "ka" ): echo 'selected'; endif; ?>>ka (Georgian)</option>
                 <option value="de" <?php if( $language["language_code"] == "de" ): echo 'selected'; endif; ?>>de (German)</option>
                 <option value="de-AT" <?php if( $language["language_code"] == "de-AT" ): echo 'selected'; endif; ?>>de-AT (German Austria)</option>
                 <option value="de-CH" <?php if( $language["language_code"] == "de-CH" ): echo 'selected'; endif; ?>>de-CH (German Switzerland)</option>
                 <option value="el" <?php if( $language["language_code"] == "el" ): echo 'selected'; endif; ?>>el (Greek)</option>
                 <option value="gu" <?php if( $language["language_code"] == "gu" ): echo 'selected'; endif; ?>>gu (Gujarati)</option>
                 <option value="iw" <?php if( $language["language_code"] == "iw" ): echo 'selected'; endif; ?>>iw (Hebrew)</option>
                 <option value="hi" <?php if( $language["language_code"] == "hi" ): echo 'selected'; endif; ?>>hi (Hindi)</option>
                 <option value="hu" <?php if( $language["language_code"] == "hu" ): echo 'selected'; endif; ?>>hu (Hungarain)</option>
                 <option value="is" <?php if( $language["language_code"] == "is" ): echo 'selected'; endif; ?>>is (Icelandic)</option>
                 <option value="id" <?php if( $language["language_code"] == "id" ): echo 'selected'; endif; ?>>id (Indonesian)</option>
                 <option value="it" <?php if( $language["language_code"] == "it" ): echo 'selected'; endif; ?>>it (Italian)</option>
                 <option value="ja" <?php if( $language["language_code"] == "ja" ): echo 'selected'; endif; ?>>ja (Japanese)</option>
                 <option value="kn" <?php if( $language["language_code"] == "kn" ): echo 'selected'; endif; ?>>kn (Kannada)</option>
                 <option value="ko" <?php if( $language["language_code"] == "ko" ): echo 'selected'; endif; ?>>ko (Korean)</option>
                 <option value="lo" <?php if( $language["language_code"] == "lo" ): echo 'selected'; endif; ?>>lo (Laothian)</option>
                 <option value="lv" <?php if( $language["language_code"] == "lv" ): echo 'selected'; endif; ?>>lv (Latvian)</option>
                 <option value="lt" <?php if( $language["language_code"] == "lt" ): echo 'selected'; endif; ?>>lt (Lithuanian)</option>
                 <option value="ms" <?php if( $language["language_code"] == "ms" ): echo 'selected'; endif; ?>>ms (Malay)</option>
                 <option value="ml" <?php if( $language["language_code"] == "ml" ): echo 'selected'; endif; ?>>ml (Malayalam)</option>
                 <option value="mr" <?php if( $language["language_code"] == "mr" ): echo 'selected'; endif; ?>>mr (Marathi)</option>
                 <option value="mn" <?php if( $language["language_code"] == "mn" ): echo 'selected'; endif; ?>>mn (Mongolian)</option>
                 <option value="no" <?php if( $language["language_code"] == "no" ): echo 'selected'; endif; ?>>no (Norwegian)</option>
                 <option value="fa" <?php if( $language["language_code"] == "fa" ): echo 'selected'; endif; ?>>fa (Persian)</option>
                 <option value="pl" <?php if( $language["language_code"] == "pl" ): echo 'selected'; endif; ?>>pl (Polish)</option>
                 <option value="pt" <?php if( $language["language_code"] == "pt" ): echo 'selected'; endif; ?>>pt (Portuguese)</option>
                 <option value="pt-BR" <?php if( $language["language_code"] == "pt-BR" ): echo 'selected'; endif; ?>>pt-BR (Portuguese Brazil)</option>
                 <option value="pt-PT" <?php if( $language["language_code"] == "pt-PT" ): echo 'selected'; endif; ?>>pt-PT (Portuguese Portugal)</option>
                 <option value="ro" <?php if( $language["language_code"] == "ro" ): echo 'selected'; endif; ?>>ro (Romanian)</option>
                 <option value="ru" <?php if( $language["language_code"] == "ru" ): echo 'selected'; endif; ?>>ru (Russian)</option>
                 <option value="sr" <?php if( $language["language_code"] == "sr" ): echo 'selected'; endif; ?>>sr (Serbian)</option>
                 <option value="si" <?php if( $language["language_code"] == "si" ): echo 'selected'; endif; ?>>si (Sinhalese)</option>
                 <option value="sk" <?php if( $language["language_code"] == "sk" ): echo 'selected'; endif; ?>>sk (Slovak)</option>
                 <option value="sl" <?php if( $language["language_code"] == "sl" ): echo 'selected'; endif; ?>>sl (Slovenian)</option>
                 <option value="es" <?php if( $language["language_code"] == "es" ): echo 'selected'; endif; ?>>es (Spanish)</option>
                 <option value="es-419" <?php if( $language["language_code"] == "es-419" ): echo 'selected'; endif; ?>>es-419 (Spanish Latin America)</option>
                 <option value="sw" <?php if( $language["language_code"] == "sw" ): echo 'selected'; endif; ?>>sw (Swahili)</option>
                 <option value="sv" <?php if( $language["language_code"] == "sv" ): echo 'selected'; endif; ?>>sv (Swedish)</option>
                 <option value="ta" <?php if( $language["language_code"] == "ta" ): echo 'selected'; endif; ?>>ta (Tamil)</option>
                 <option value="te" <?php if( $language["language_code"] == "te" ): echo 'selected'; endif; ?>>te (Telugu)</option>
                 <option value="th" <?php if( $language["language_code"] == "th" ): echo 'selected'; endif; ?>>th (Thai)</option>
                 <option value="tr" <?php if( $language["language_code"] == "tr" ): echo 'selected'; endif; ?>>tr (Turkish)</option>
                 <option value="uk" <?php if( $language["language_code"] == "uk" ): echo 'selected'; endif; ?>>uk (Ukrainian)</option>
                 <option value="ur" <?php if( $language["language_code"] == "ur" ): echo 'selected'; endif; ?>>ur (Urdu)</option>
                 <option value="vi" <?php if( $language["language_code"] == "vi" ): echo 'selected'; endif; ?>>vi (Vietnamese)</option>
                 <option value="zu" <?php if( $language["language_code"] == "zu" ): echo 'selected'; endif; ?>>zu (Zulu)</option>
              </select>
           </div>
           <hr>
            <?php foreach( $languageArray as $key => $val ): ?>
              <div class="form-group">
                 <label class="control-label"><?php echo $key; ?></label>
                 <input type="text" class="form-control" name="Language[<?php echo $key; ?>]" value="<?php echo $val;?>">
              </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary">Update</button>
         </form>
      </div>
   </div>
</div>
<?php endif; ?>
