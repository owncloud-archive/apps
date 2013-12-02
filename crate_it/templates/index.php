<div id="container">

<button id="help_button" style="float:right;"> ? </button>

<div style="padding-top:20px; padding-left:5px;">
    <form id="crate_input" method="get">
        Create new crate:
        <input type="text" id="create">
        <input id="subbutton" type="submit" value="Submit">
    </form>
    <select id="crates">
        <?php foreach($_['crates'] as $crate):?>
        <option id="<?php echo $crate; ?>" value="<?php echo $crate; ?>" <?php if($_['selected_crate']==$crate){echo 'selected';}?>>
            <?php echo $crate;?>
        </option>
        <?php endforeach;?>
    </select>
    <input id="delete" type="button" value="Delete Crate" />
    <input id="clear" type="button" value="Clear Crate" />
    <?php if ($_['previews']==="on" ):?>
    <input id="epub" type="button" value="EPUB" />
    <?php endif; ?>
    <input id="download" type="button" value="Download Crate as zip" />
    <?php if ($_['sword_status'] === "enabled" ):?>
        <div>
            <select id="sword_collection">
                <?php foreach ($_['sword_collections'] as $collection => $href): ?>
                    <option value="<?php echo $href?>">
                        <?php echo $collection; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input id="post" type="button" value="Post Crate to SWORD" />

    <?php endif; ?>
</div>

    <div id="metadata" style="float:right;">
        <?php if ($_['mint_status'] === "enabled" ):?>

    		<div id="anzsrc_for">
    		    <div>
    		        <select id="for_top_level" class="for_codes">
    		            <option id="select_top" value="for_top_choose">Choose a code</option>
    		            <?php foreach($_['top_for'] as $item): $vars=get_object_vars($item); //$prefLabel=$ vars[ 'skos:prefLabel']; ?>
    		            <option id="<?php echo $vars['rdf:about'];?>" value="<?php echo $vars['rdf:about'];?>">
    		                <?php echo $vars[ 'skos:prefLabel']?>
    		            </option>
    		            <?php endforeach;?>
    		        </select>
    		    </div>
    		    <div>
    		        <select id="for_second_level" class="for_codes">
    		            <option id="select_second" value="for_second_choose">Choose a code</option>
    		        </select>
    		    </div>
    		    <div>
    		        <select id="for_third_level" class="for_codes">
    		            <option id="select_third" value="for_third_choose">Choose a code</option>
    		        </select>
    		    </div>
    		</div>

    		<div id="creators_box">
    		    <div>
    		        <label for="creators">Add Data Creator/s</label>
    		    </div>
    		    <ul id="creators">
                        <?php foreach($_['creators'] as $creator):?>
    		    	  <li>
    			    <input id="creator_<?php echo $creator['creator_id'] ?>" type="button" value="Remove" />
    			    <span id="<?php echo $creator['creator_id'] ?>" class="full_name"><?php echo $creator['full_name'] ?></span>
    			  </li>
                        <?php endforeach;?>
    		    </ul>
    		</div>

    		<div id="search_people_box">
    		    <input id="keyword" type="text" name="keyword" />
    		    <input id="search_people" type="button" value="Search People" />
    		</div>

    		<div id="search_people_result_box">
    		    <ul id="search_people_results">
    		    </ul>
    		</div>

        <?php endif; ?>

</div>

    <div>
        <div style="padding-top:20px; padding-left:5px;">
            <span id="crateName" style="font-weight:bold;font-size:large; padding-left:10px;"><?php echo $_['selected_crate'] ?></span>
            <div id='#description_box'>
                <label for="description">Description</label>
		<input id="edit_description" type="button" value="Edit" />
                <div id="description"><?php echo htmlentities($_['description']) ?></div>
            </div>
        </div>

        <div style="float:left; padding-left:20px; padding-top:5px;">
            <div id="files"></div>
            <span>Crate size: </span><span id="crate_size_human">/span>
        </div>
    </div>

</div>



<div>
    <ul id="fileMenu" class="dropdown-menu" role="menu" aria-labelledby="dLabel">
        <li class="add"><a href="#add"><i class=".glyphicon .glyphicon-plus"></i> Add</a></li>
        <li class="rename"><a href="#rename"><i class=".glyphicon .glyphicon-edit"></i> Rename</a></li>
        <li class="divider"></li>
        <li class="delete"><a href="#delete"><i class=".glyphicon .glyphicon-floppy-remove"></i> Delete</a></li>
    </ul>
</div>

<div id="dialog-add" title="Add Folder">
    <p><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>New folder name:</p>
    <input id="add-folder" type="text"></input>
</div>
<div id="dialog-rename" title="Rename Item">
    <p><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>New name:</p>
    <input id="rename-item" type="text"></input>
</div>
<div id="dialog-delete" title="Remove Item">
    <p><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>Remove item from crate?</p>
</div>

<div id="dialog-help" title="Cr8it Help">
    <p>
        <b>Create New Data Crate</b>
        <ul>
            <li>Enter the name of your crate and click <b><i>Submit</i></b></li>
            <li>Select your crate name from the <b><i>default_crate</i></b> dropdown menu</li>
        </ul>
    </p>
    
    <p>
        <b>Describe Your Crate</b>
        <ul>
            <li>Click <b><i>Edit</i></b> to enter a description of the data in your crate. Include information about the research dataset and its characteristics and features</li>
            <li>Click <b><i>Save</i></b></li>
            <li>Search/add the grant ID/number associated with your data if relevant</li>
            <li>Search/add names of Data Creator/s</li>
        </ul>
    </p>

    <p>
        <b>Add Files to Data Crate</b>
        <ul>
            <li>Select <b><i>Files</i></b></li>
            <li>Navigate to the file or folder you wish to add</li>
            <li>Hover your mouse over the file/folder and select <b><i>Add to Crate</i></b></li>
            <li>Add all desired files to crate</li>
            <li>Select <b><i>Cr8it</i></b> to view your crate</li>
        </ul>
    </p>

    <p>
        <b>Delete a Crate</b>
        <ul>
            <li>Select <b><i>Cr8it</i></b></li>
            <li>Select crate from the <b><i>default_crate</i></b> dropdown menu</li>
            <li>Select <b><i>Delete Crate</i></b></li>
        </ul>
    </p>
</div>


<!-- workaround to make var avalaide to javascript -->
<div id="hidden_vars" hidden="hidden">
    <span id="description_length"><?php echo $_['description_length']; ?></span>
    <span id="max_sword_mb"><?php echo $_['max_sword_mb'] ?></span>
    <span id="max_zip_mb"><?php echo $_['max_zip_mb'] ?></span>
</div>


