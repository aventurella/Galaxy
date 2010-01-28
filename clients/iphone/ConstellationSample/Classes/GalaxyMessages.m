//
//  GalaxyMessages.m
//  ConstellationSample
//
//  Created by Adam Venturella on 1/27/10.
//  Copyright 2010 __MyCompanyName__. All rights reserved.
//

#import "GalaxyMessages.h"
#import "Constellation.h"
#import "NSObject+YAJL.h"

@implementation GalaxyMessages


- (id)initWithTopic:(NSString *)target constellation:(Constellation *)cn
{
	if(self = [super initWithNibName:nil bundle:nil])
	{
		constellation = cn;
		topic = target;
		
		[topic retain];
		[constellation retain];
	}
	
	return self;
}

/*
- (id)initWithStyle:(UITableViewStyle)style {
    // Override initWithStyle: if you create the controller programmatically and want to perform customization that is not appropriate for viewDidLoad.
    if (self = [super initWithStyle:style]) {
    }
    return self;
}
*/


- (void)viewDidLoad 
{
    [super viewDidLoad];
	self.navigationItem.title = @"Galaxy Messages";
	constellation.delegate    = self;
	[constellation messages:topic page:@"1" limit:@"25"];
}

- (BOOL)constellationShouldGetMessages:(Constellation *)constellation forTopic:(NSString *)topic
{
	return YES;
}

- (void)constellationDidGetMessages:(NSData *)data
{
	NSDictionary *results = [data yajl_JSON];
	
	if([results valueForKey:@"ok"])
	{
		dataProvider = [results valueForKey:@"response"];
		[dataProvider retain];
		[self.tableView reloadData];
	}
}



/*
- (void)viewWillAppear:(BOOL)animated {
    [super viewWillAppear:animated];
}
*/
/*
- (void)viewDidAppear:(BOOL)animated {
    [super viewDidAppear:animated];
}
*/
/*
- (void)viewWillDisappear:(BOOL)animated {
	[super viewWillDisappear:animated];
}
*/
/*
- (void)viewDidDisappear:(BOOL)animated {
	[super viewDidDisappear:animated];
}
*/

/*
// Override to allow orientations other than the default portrait orientation.
- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation {
    // Return YES for supported orientations
    return (interfaceOrientation == UIInterfaceOrientationPortrait);
}
*/

- (void)didReceiveMemoryWarning {
	// Releases the view if it doesn't have a superview.
    [super didReceiveMemoryWarning];
	
	// Release any cached data, images, etc that aren't in use.
}

- (void)viewDidUnload {
	// Release any retained subviews of the main view.
	// e.g. self.myOutlet = nil;
}


#pragma mark Table view methods

- (NSInteger)numberOfSectionsInTableView:(UITableView *)tableView {
    return 1;
}


// Customize the number of rows in the table view.
- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section {
    return [dataProvider count];
}


// Customize the appearance of table view cells.
- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath {
    
    static NSString *CellIdentifier = @"Cell";
    
    UITableViewCell *cell = [tableView dequeueReusableCellWithIdentifier:CellIdentifier];
    if (cell == nil) {
        cell = [[[UITableViewCell alloc] initWithStyle:UITableViewCellStyleSubtitle reuseIdentifier:CellIdentifier] autorelease];
    }
    
    // Set up the cell...
	NSDictionary * current = [dataProvider objectAtIndex:indexPath.row];
	
	NSString * author         = [[current valueForKey:@"author"] valueForKey:@"name"];
	NSString * originName     = [[current valueForKey:@"source"] valueForKey:@"description"]; 
	NSString * body           = [current valueForKey:@"body"]; 
	
	cell.textLabel.font = [UIFont fontWithName:cell.textLabel.font.fontName size:12.0];
	cell.detailTextLabel.font = [UIFont fontWithName:cell.detailTextLabel.font.fontName size:10.0];
	
	cell.textLabel.text       = [current valueForKey:@"title"];
	cell.detailTextLabel.text = [NSString stringWithFormat:@"Created By:%@ on %@ | %@", author, originName, body ];
    return cell;
}


- (void)tableView:(UITableView *)tableView didSelectRowAtIndexPath:(NSIndexPath *)indexPath {
    // Navigation logic may go here. Create and push another view controller.
	// AnotherViewController *anotherViewController = [[AnotherViewController alloc] initWithNibName:@"AnotherView" bundle:nil];
	// [self.navigationController pushViewController:anotherViewController];
	// [anotherViewController release];
}


/*
// Override to support conditional editing of the table view.
- (BOOL)tableView:(UITableView *)tableView canEditRowAtIndexPath:(NSIndexPath *)indexPath {
    // Return NO if you do not want the specified item to be editable.
    return YES;
}
*/


/*
// Override to support editing the table view.
- (void)tableView:(UITableView *)tableView commitEditingStyle:(UITableViewCellEditingStyle)editingStyle forRowAtIndexPath:(NSIndexPath *)indexPath {
    
    if (editingStyle == UITableViewCellEditingStyleDelete) {
        // Delete the row from the data source
        [tableView deleteRowsAtIndexPaths:[NSArray arrayWithObject:indexPath] withRowAnimation:YES];
    }   
    else if (editingStyle == UITableViewCellEditingStyleInsert) {
        // Create a new instance of the appropriate class, insert it into the array, and add a new row to the table view
    }   
}
*/


/*
// Override to support rearranging the table view.
- (void)tableView:(UITableView *)tableView moveRowAtIndexPath:(NSIndexPath *)fromIndexPath toIndexPath:(NSIndexPath *)toIndexPath {
}
*/


/*
// Override to support conditional rearranging of the table view.
- (BOOL)tableView:(UITableView *)tableView canMoveRowAtIndexPath:(NSIndexPath *)indexPath {
    // Return NO if you do not want the item to be re-orderable.
    return YES;
}
*/


- (void)dealloc {
	[topic release];
	[constellation release];
	[dataProvider release];
    [super dealloc];
}


@end

